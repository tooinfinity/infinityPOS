<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Data\Inventory\CreateInventoryLayerData;
use App\Enums\StockTransferStatusEnum;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CancelStockTransfer
{
    public function __construct(
        private DeductFromLayers $deductFromLayers,
        private CreateInventoryLayer $createInventoryLayer,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(StockTransfer $transfer, int $userId): StockTransfer
    {
        if ($transfer->isCancelled()) {
            return $transfer;
        }

        return DB::transaction(function () use ($transfer, $userId): StockTransfer {
            // If transfer was completed, reverse the stock movements
            if ($transfer->isCompleted()) {
                foreach ($transfer->items as $item) {
                    // Deduct from destination store (reverse)
                    $this->deductFromLayers->handle(
                        product: $item->product_id,
                        store: $transfer->to_store_id,
                        quantity: $item->quantity,
                        batchNumber: $item->batch_number
                    );

                    // Create reversal stock movement (outgoing from destination)
                    StockMovement::query()->create([
                        'product_id' => $item->product_id,
                        'store_id' => $transfer->to_store_id,
                        'quantity' => -$item->quantity,
                        'source_type' => StockTransfer::class,
                        'source_id' => $transfer->id,
                        'batch_number' => $item->batch_number,
                        'notes' => 'Transfer cancelled (reversal): '.$transfer->reference,
                        'created_by' => $userId,
                    ]);

                    // Add back to source store
                    $this->createInventoryLayer->handle(
                        new CreateInventoryLayerData(
                            product_id: $item->product_id,
                            store_id: $transfer->from_store_id,
                            batch_number: $item->batch_number,
                            expiry_date: null,
                            unit_cost: 0,
                            received_qty: $item->quantity,
                            remaining_qty: $item->quantity,
                            received_at: now()->toDateTimeString(),
                        )
                    );

                    // Create reversal stock movement (incoming to source)
                    StockMovement::query()->create([
                        'product_id' => $item->product_id,
                        'store_id' => $transfer->from_store_id,
                        'quantity' => $item->quantity,
                        'source_type' => StockTransfer::class,
                        'source_id' => $transfer->id,
                        'batch_number' => $item->batch_number,
                        'notes' => 'Transfer cancelled (reversal): '.$transfer->reference,
                        'created_by' => $userId,
                    ]);
                }
            }

            $transfer->update([
                'status' => StockTransferStatusEnum::CANCELLED,
                'updated_by' => $userId,
            ]);

            return $transfer;
        });
    }
}
