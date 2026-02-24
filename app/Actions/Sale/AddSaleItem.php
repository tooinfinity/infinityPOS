<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Data\Sale\SaleItemData;
use App\Enums\SaleStatusEnum;
use App\Models\Batch;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class AddSaleItem
{
    /**
     * @throws Throwable
     */
    public function handle(Sale $sale, SaleItemData $data): SaleItem
    {
        return DB::transaction(function () use ($sale, $data): SaleItem {
            $this->validateSaleIsPending($sale);

            $this->validateStockAvailability($sale, $data);

            $item = SaleItem::query()->forceCreate([
                'sale_id' => $sale->id,
                'product_id' => $data->product_id,
                'batch_id' => $data->batch_id,
                'quantity' => $data->quantity,
                'unit_price' => $data->unit_price,
                'unit_cost' => $data->unit_cost,
                'subtotal' => $data->quantity * $data->unit_price,
            ]);

            $this->recalculateSaleTotals($sale);

            return $item;
        });
    }

    private function validateSaleIsPending(Sale $sale): void
    {
        if ($sale->status !== SaleStatusEnum::Pending) {
            throw new RuntimeException(
                "Can only add items to pending sales. Current status: {$sale->status->value}"
            );
        }
    }

    /**
     * @throws Throwable
     */
    private function validateStockAvailability(Sale $sale, SaleItemData $data): void
    {
        $batch = Batch::query()
            ->lockForUpdate()
            ->find($data->batch_id);

        if ($batch === null) {
            throw new RuntimeException("Batch not found for product $data->product_id");
        }

        if ($batch->product_id !== $data->product_id) {
            throw new RuntimeException(
                "Batch does not belong to product $data->product_id"
            );
        }

        throw_if($batch->warehouse_id !== $sale->warehouse_id, RuntimeException::class, "Batch is not in the sale's warehouse");

        $sale->loadMissing('items');
        /** @var int $existingQuantity */
        $existingQuantity = $sale->items
            ->where('batch_id', $data->batch_id)
            ->sum('quantity');

        $availableQuantity = $batch->quantity - $existingQuantity;

        if ($availableQuantity < $data->quantity) {
            throw new RuntimeException(
                "Insufficient stock in batch. Required: $data->quantity, Available: $availableQuantity"
            );
        }
    }

    private function recalculateSaleTotals(Sale $sale): void
    {
        $sale->load('items');
        $totalAmount = $sale->items->sum('subtotal');
        $sale->forceFill(['total_amount' => $totalAmount])->save();
    }
}
