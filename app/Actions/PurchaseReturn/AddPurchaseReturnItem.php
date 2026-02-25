<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturn;

use App\Data\PurchaseReturn\PurchaseReturnItemData;
use App\Enums\ReturnStatusEnum;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class AddPurchaseReturnItem
{
    /**
     * @throws Throwable
     */
    public function handle(PurchaseReturn $purchaseReturn, PurchaseReturnItemData $data): PurchaseReturnItem
    {
        return DB::transaction(function () use ($purchaseReturn, $data): PurchaseReturnItem {
            /** @var PurchaseReturn $purchaseReturn */
            $purchaseReturn = PurchaseReturn::query()
                ->lockForUpdate()
                ->with('purchase.items')
                ->findOrFail($purchaseReturn->id);

            $this->validatePurchaseReturnIsPending($purchaseReturn);
            $this->validateAgainstOriginalPurchase($purchaseReturn, $data);

            $item = PurchaseReturnItem::query()->forceCreate([
                'purchase_return_id' => $purchaseReturn->id,
                'product_id' => $data->product_id,
                'batch_id' => $data->batch_id,
                'quantity' => $data->quantity,
                'unit_cost' => $data->unit_cost,
                'subtotal' => $data->quantity * $data->unit_cost,
            ]);

            $this->recalculateTotalAmount($purchaseReturn);

            return $item;
        });
    }

    /**
     * @throws Throwable
     */
    private function validatePurchaseReturnIsPending(PurchaseReturn $purchaseReturn): void
    {
        throw_if($purchaseReturn->status !== ReturnStatusEnum::Pending, RuntimeException::class, 'Cannot add items to a non-pending purchase return.');
    }

    /**
     * @throws Throwable
     */
    private function validateAgainstOriginalPurchase(PurchaseReturn $purchaseReturn, PurchaseReturnItemData $data): void
    {
        $purchase = $purchaseReturn->purchase;

        throw_if($purchase === null, RuntimeException::class, 'Purchase return must be associated with a purchase.');

        /** @var PurchaseItem|null $originalPurchaseItem */
        $originalPurchaseItem = $purchase->items
            ->where('product_id', $data->product_id)
            ->where('batch_id', $data->batch_id)
            ->first();

        throw_if($originalPurchaseItem === null, RuntimeException::class, 'Product is not part of the original purchase or batch does not match.');

        $alreadyReturned = $purchaseReturn->items()
            ->where('product_id', $data->product_id)
            ->where('batch_id', $data->batch_id)
            ->sum('quantity');

        $maxReturnable = $originalPurchaseItem->quantity - $alreadyReturned;

        throw_if($data->quantity > $maxReturnable, RuntimeException::class, "Cannot return more than originally purchased. Original: {$originalPurchaseItem->quantity}, Already returned: {$alreadyReturned}, Remaining: {$maxReturnable}");
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
