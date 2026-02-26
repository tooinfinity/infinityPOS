<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Data\SaleReturn\UpdateSaleReturnItemData;
use App\Enums\ReturnStatusEnum;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class UpdateSaleReturnItem
{
    /**
     * @throws Throwable
     */
    public function handle(SaleReturnItem $item, UpdateSaleReturnItemData $data): SaleReturnItem
    {
        return DB::transaction(function () use ($item, $data): SaleReturnItem {
            /** @var SaleReturnItem $item */
            $item = SaleReturnItem::query()
                ->lockForUpdate()
                ->with('saleReturn.sale.items')
                ->findOrFail($item->id);

            $saleReturn = $item->saleReturn;

            $this->validateSaleReturnIsPending($saleReturn);

            $quantity = $data->quantity ?? $item->quantity;
            $unitPrice = $data->unit_price ?? $item->unit_price;

            if ($data->quantity !== null) {
                $this->validateQuantityAgainstOriginalSale($item, $quantity);
            }

            $item->forceFill([
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $quantity * $unitPrice,
            ])->save();

            $this->recalculateTotalAmount($saleReturn);

            return $item->refresh();
        });
    }

    /**
     * @throws Throwable
     */
    private function validateQuantityAgainstOriginalSale(SaleReturnItem $item, int $newQuantity): void
    {
        $saleReturn = $item->saleReturn;
        /** @var Sale|null $sale */
        $sale = $saleReturn->sale;

        throw_if($sale === null, RuntimeException::class, 'Sale return must be associated with a sale.');

        /** @var SaleItem|null $originalSaleItem */
        $originalSaleItem = $sale->items
            ->where('product_id', $item->product_id)
            ->where('batch_id', $item->batch_id)
            ->first();

        throw_if($originalSaleItem === null, RuntimeException::class, 'Product is not part of the original sale or batch does not match.');

        $alreadyReturnedExcludingCurrent = SaleReturnItem::query()
            ->whereHas('saleReturn', fn (Builder $q) => $q->where('sale_id', $sale->id))
            ->where('product_id', $item->product_id)
            ->where('batch_id', $item->batch_id)
            ->where('id', '!=', $item->id)
            ->sum('quantity');

        $maxReturnable = $originalSaleItem->quantity - $alreadyReturnedExcludingCurrent;

        throw_if($newQuantity > $maxReturnable, RuntimeException::class, "Cannot return more than originally purchased. Original: {$originalSaleItem->quantity}, Already returned (excluding current): {$alreadyReturnedExcludingCurrent}, Max returnable: {$maxReturnable}");
    }

    /**
     * @throws Throwable
     */
    private function validateSaleReturnIsPending(SaleReturn $saleReturn): void
    {
        throw_if($saleReturn->status !== ReturnStatusEnum::Pending, RuntimeException::class, 'Cannot update items in a non-pending sale return.');
    }

    private function recalculateTotalAmount(SaleReturn $saleReturn): void
    {
        $saleReturn->refresh();

        $totalAmount = $saleReturn->items()->lockForUpdate()->sum('subtotal');

        $saleReturn->forceFill([
            'total_amount' => $totalAmount,
        ])->save();
    }
}
