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
use Throwable;

final readonly class UpdateSaleItemAction
{
    /**
     * @throws Throwable
     */
    public function handle(SaleItem $item, UpdateSaleItemData $data): SaleItem
    {
        return DB::transaction(function () use ($item, $data): SaleItem {
            $this->validateSaleIsPending($item->sale);

            if ($data->batch_id !== null && $data->batch_id !== $item->batch_id) {
                $this->validateStockAvailability($data->batch_id, $data->quantity ?? $item->quantity);
            } elseif ($data->quantity !== null && $data->quantity > $item->quantity && $item->batch_id !== null) {
                $additionalQty = $data->quantity - $item->quantity;
                $this->validateStockAvailability($item->batch_id, $additionalQty);
            }

            $quantity = $data->quantity ?? $item->quantity;
            $unitPrice = $data->unit_price ?? $item->unit_price;
            $unitCost = $data->unit_cost ?? $item->unit_cost;
            $batchId = $data->batch_id ?? $item->batch_id;

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
    private function validateStockAvailability(int $batchId, int $quantity): void
    {
        $batch = Batch::query()->find($batchId);

        throw_if($batch === null, RuntimeException::class, "Batch not found: {$batchId}");

        if ($batch->quantity < $quantity) {
            throw new RuntimeException(
                "Insufficient stock in batch. Required: {$quantity}, Available: {$batch->quantity}"
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
