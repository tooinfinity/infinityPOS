<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Data\PurchaseReturn\UpdatePurchaseReturnItemData;
use App\Enums\ReturnStatusEnum;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class UpdatePurchaseReturnItem
{
    /**
     * @throws Throwable
     */
    public function handle(PurchaseReturnItem $item, UpdatePurchaseReturnItemData $data): PurchaseReturnItem
    {
        return DB::transaction(function () use ($item, $data): PurchaseReturnItem {
            /** @var PurchaseReturnItem $item */
            $item = PurchaseReturnItem::query()
                ->lockForUpdate()
                ->with('purchaseReturn.purchase.items')
                ->findOrFail($item->id);

            $purchaseReturn = $item->purchaseReturn;

            $this->validatePurchaseReturnIsPending($purchaseReturn);

            $quantity = $data->quantity ?? $item->quantity;
            $unitCost = $data->unit_cost ?? $item->unit_cost;

            if ($data->quantity !== null) {
                $this->validateQuantityAgainstOriginalPurchase($item, $quantity);
            }

            $item->forceFill([
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'subtotal' => $quantity * $unitCost,
            ])->save();

            $this->recalculateTotalAmount($purchaseReturn);

            return $item->refresh();
        });
    }

    /**
     * @throws Throwable
     */
    private function validateQuantityAgainstOriginalPurchase(PurchaseReturnItem $item, int $newQuantity): void
    {
        $purchaseReturn = $item->purchaseReturn;
        /** @var Purchase|null $purchase */
        $purchase = $purchaseReturn->purchase;

        throw_if($purchase === null, RuntimeException::class, 'Purchase return must be associated with a purchase.');

        /** @var PurchaseItem|null $originalPurchaseItem */
        $originalPurchaseItem = $purchase->items
            ->where('product_id', $item->product_id)
            ->where('batch_id', $item->batch_id)
            ->first();

        throw_if($originalPurchaseItem === null, RuntimeException::class, 'Product is not part of the original purchase or batch does not match.');

        $alreadyReturnedExcludingCurrent = PurchaseReturnItem::query()
            ->whereHas('purchaseReturn', fn (Builder $q) => $q->where('purchase_id', $purchase->id))
            ->where('product_id', $item->product_id)
            ->where('batch_id', $item->batch_id)
            ->where('id', '!=', $item->id)
            ->sum('quantity');

        $maxReturnable = $originalPurchaseItem->quantity - $alreadyReturnedExcludingCurrent;

        throw_if($newQuantity > $maxReturnable, RuntimeException::class, "Cannot return more than originally purchased. Original: {$originalPurchaseItem->quantity}, Already returned (excluding current): {$alreadyReturnedExcludingCurrent}, Max returnable: {$maxReturnable}");
    }

    /**
     * @throws Throwable
     */
    private function validatePurchaseReturnIsPending(PurchaseReturn $purchaseReturn): void
    {
        throw_if($purchaseReturn->status !== ReturnStatusEnum::Pending, RuntimeException::class, 'Cannot update items in a non-pending purchase return.');
    }

    private function recalculateTotalAmount(PurchaseReturn $purchaseReturn): void
    {
        $purchaseReturn->refresh();

        $totalAmount = $purchaseReturn->items()->lockForUpdate()->sum('subtotal');

        $purchaseReturn->forceFill([
            'total_amount' => $totalAmount,
        ])->save();
    }
}
