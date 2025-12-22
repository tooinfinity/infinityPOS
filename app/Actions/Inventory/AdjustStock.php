<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Data\Inventory\AdjustStockData;
use App\Data\Inventory\CreateInventoryLayerData;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class AdjustStock
{
    public function __construct(
        private DeductFromLayers $deductFromLayers,
        private CreateInventoryLayer $createInventoryLayer,
    ) {}

    /**
     * Adjust stock levels (positive for increase, negative for decrease).
     *
     * @throws Throwable
     */
    public function handle(AdjustStockData $data): StockMovement
    {
        throw_if($data->quantity === 0, InvalidArgumentException::class, 'Adjustment quantity cannot be zero.');

        return DB::transaction(function () use ($data): StockMovement {
            if ($data->quantity < 0) {
                // Decrease stock - deduct from layers
                $this->deductFromLayers->handle(
                    product: $data->product_id,
                    store: $data->store_id,
                    quantity: abs($data->quantity),
                    batchNumber: $data->batch_number
                );
            } else {
                // Increase stock - create new layer
                $this->createInventoryLayer->handle(
                    new CreateInventoryLayerData(
                        product_id: $data->product_id,
                        store_id: $data->store_id,
                        batch_number: $data->batch_number,
                        expiry_date: null,
                        unit_cost: 0, // Adjustments don't have cost
                        received_qty: $data->quantity,
                        remaining_qty: $data->quantity,
                        received_at: now()->toDateTimeString(),
                    )
                );
            }

            // Create stock movement record
            return StockMovement::query()->create([
                'product_id' => $data->product_id,
                'store_id' => $data->store_id,
                'quantity' => $data->quantity,
                'source_type' => null, // Adjustment has no source
                'source_id' => null,
                'batch_number' => $data->batch_number,
                'notes' => $this->buildNotes($data),
                'created_by' => $data->created_by,
            ]);
        });
    }

    private function buildNotes(AdjustStockData $data): string
    {
        $notes = 'Stock adjustment';

        if ($data->reason !== null) {
            $notes .= ': '.$data->reason;
        }

        if ($data->notes !== null) {
            $notes .= ' - '.$data->notes;
        }

        return $notes;
    }
}
