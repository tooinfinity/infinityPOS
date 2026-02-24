<?php

declare(strict_types=1);

namespace App\Actions\Sale;

use App\Data\Sale\UpdateSaleItemData;
use App\Enums\SaleStatusEnum;
use App\Models\Batch;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Spatie\LaravelData\Optional;
use Throwable;

final readonly class UpdateSaleItem
{
    /**
     * @throws Throwable
     */
    public function handle(SaleItem $item, UpdateSaleItemData $data): SaleItem
    {
        return DB::transaction(function () use ($item, $data): SaleItem {
            $this->validateSaleIsPending($item->sale);

            $batchIdValue = $data->batch_id;
            $quantityValue = $data->quantity;

            $isBatchIdProvided = $batchIdValue !== null && ! $batchIdValue instanceof Optional;
            $isQuantityProvided = $quantityValue !== null && ! $quantityValue instanceof Optional;

            $batchIdChanged = $isBatchIdProvided && $batchIdValue !== $item->batch_id;
            $quantityIncreased = $isQuantityProvided && $quantityValue > $item->quantity;

            if ($batchIdChanged) {
                $quantity = $isQuantityProvided ? $quantityValue : $item->quantity;
                /** @var int $batchIdValue */
                /** @var int $quantity */
                $this->validateStockAvailability($item->sale, $batchIdValue, $quantity);
            } elseif ($quantityIncreased && $item->batch_id !== null) {
                /** @var int $quantityValue */
                $this->validateStockAvailability($item->sale, $item->batch_id, $quantityValue);
            }

            $quantity = is_int($data->quantity) ? $data->quantity : $item->quantity;
            $unitPrice = is_numeric($data->unit_price) ? $data->unit_price : $item->unit_price;
            $unitCost = is_numeric($data->unit_cost) ? $data->unit_cost : $item->unit_cost;
            $batchId = is_int($data->batch_id) ? $data->batch_id : $item->batch_id;

            $item->forceFill([
                'batch_id' => $batchId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'unit_cost' => $unitCost,
                'subtotal' => $quantity * $unitPrice,
            ])->save();

            $this->recalculateSaleTotals($item->sale);

            return $item->refresh();
        });
    }

    private function validateSaleIsPending(Sale $sale): void
    {
        if ($sale->status !== SaleStatusEnum::Pending) {
            throw new RuntimeException(
                "Can only update items in pending sales. Current status: {$sale->status->value}"
            );
        }
    }

    /**
     * @throws Throwable
     */
    private function validateStockAvailability(Sale $sale, int $batchId, int $quantity): void
    {
        $batch = Batch::query()
            ->lockForUpdate()
            ->find($batchId);

        throw_if($batch === null, RuntimeException::class, "Batch not found: $batchId");

        throw_if($batch->warehouse_id !== $sale->warehouse_id, RuntimeException::class, "Batch is not in the sale's warehouse");

        if ($batch->quantity < $quantity) {
            throw new RuntimeException(
                "Insufficient stock in batch. Required: $quantity, Available: $batch->quantity"
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
