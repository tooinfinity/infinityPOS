<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Data\Sale\SaleData;
use App\Data\Sale\SaleItemData;
use App\Enums\SaleStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdateSale
{
    /**
     * @throws Throwable
     */
    public function handle(Sale $sale, SaleData $data): Sale
    {
        return DB::transaction(static function () use ($sale, $data): Sale {
            if ($sale->status !== SaleStatusEnum::Pending) {
                throw new InvalidOperationException(
                    'update',
                    'Sale',
                    "Cannot edit a sale with status: {$sale->status->label()}."
                );
            }

            $updatedSaleData = [
                'customer_id' => $data->customer_id,
                'warehouse_id' => $data->warehouse_id ?? $sale->warehouse_id,
                'status' => $data->status ?? $sale->status,
                'sale_date' => $data->sale_date ?? $sale->sale_date,
                'total_amount' => $data->total_amount ?? $sale->total_amount,
                'note' => $data->note ?? $sale->note,
            ];

            $sale->update($updatedSaleData);

            $sale->items()->delete();

            $data->items->toCollection()->each(function (SaleItemData $itemData) use ($sale): void {
                $sale->items()->forceCreate([
                    'product_id' => $itemData->product_id,
                    'batch_id' => $itemData->batch_id,
                    'quantity' => $itemData->quantity,
                    'unit_price' => $itemData->unit_price,
                    'unit_cost' => $itemData->unit_cost,
                    'subtotal' => $itemData->unit_price * $itemData->quantity,
                ]);
            });

            return $sale->load(['items.product', 'items.batch']);
        });
    }
}
