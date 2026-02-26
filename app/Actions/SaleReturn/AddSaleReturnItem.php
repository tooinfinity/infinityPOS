<?php

declare(strict_types=1);

namespace App\Actions\SaleReturn;

use App\Data\SaleReturn\SaleReturnItemData;
use App\Enums\ReturnStatusEnum;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class AddSaleReturnItem
{
    /**
     * @throws Throwable
     */
    public function handle(SaleReturn $saleReturn, SaleReturnItemData $data): SaleReturnItem
    {
        return DB::transaction(function () use ($saleReturn, $data): SaleReturnItem {
            /** @var SaleReturn $saleReturn */
            $saleReturn = SaleReturn::query()
                ->lockForUpdate()
                ->with('sale.items')
                ->findOrFail($saleReturn->id);

            $this->validateSaleReturnIsPending($saleReturn);
            $this->validateAgainstOriginalSale($saleReturn, $data);

            $item = SaleReturnItem::query()->forceCreate([
                'sale_return_id' => $saleReturn->id,
                'product_id' => $data->product_id,
                'batch_id' => $data->batch_id,
                'quantity' => $data->quantity,
                'unit_price' => $data->unit_price,
                'subtotal' => $data->quantity * $data->unit_price,
            ]);

            $this->recalculateTotalAmount($saleReturn);

            return $item;
        });
    }

    /**
     * @throws Throwable
     */
    private function validateSaleReturnIsPending(SaleReturn $saleReturn): void
    {
        throw_if($saleReturn->status !== ReturnStatusEnum::Pending, RuntimeException::class, 'Cannot add items to a non-pending sale return.');
    }

    /**
     * @throws Throwable
     */
    private function validateAgainstOriginalSale(SaleReturn $saleReturn, SaleReturnItemData $data): void
    {
        /** @var Sale|null $sale */
        $sale = $saleReturn->sale;

        throw_if($sale === null, RuntimeException::class, 'Sale return must be associated with a sale.');

        /** @var SaleItem|null $originalSaleItem */
        $originalSaleItem = $sale->items
            ->where('product_id', $data->product_id)
            ->where('batch_id', $data->batch_id)
            ->first();

        throw_if($originalSaleItem === null, RuntimeException::class, 'Product is not part of the original sale or batch does not match.');

        $alreadyReturned = SaleReturnItem::query()
            ->whereHas('saleReturn', fn (Builder $q) => $q->where('sale_id', $sale->id))
            ->where('product_id', $data->product_id)
            ->where('batch_id', $data->batch_id)
            ->sum('quantity');

        $maxReturnable = $originalSaleItem->quantity - $alreadyReturned;

        throw_if($data->quantity > $maxReturnable, RuntimeException::class, "Cannot return more than originally purchased. Original: {$originalSaleItem->quantity}, Already returned: {$alreadyReturned}, Remaining: {$maxReturnable}");
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
