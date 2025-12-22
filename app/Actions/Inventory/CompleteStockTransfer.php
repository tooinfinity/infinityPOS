<?php

declare(strict_types=1);

namespace App\Actions\Inventory;

use App\Enums\StockTransferStatusEnum;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class CompleteStockTransfer
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
        if ($transfer->isCompleted()) {
            return $transfer;
        }

        throw_if($transfer->isCancelled(), InvalidArgumentException::class, 'Cannot complete a cancelled transfer.');

        return DB::transaction(function () use ($transfer, $userId): StockTransfer {
            foreach ($transfer->items as $item) {
                // Deduct from source store using FIFO
                $this->deductFromLayers->handle(
                    product: $item->product_id,
                    store: $transfer->from_store_id,
                    quantity: $item->quantity,
                    batchNumber: $item->batch_number
                );

                // Create stock movement (outgoing from source)
                StockMovement::query()->create([
                    'product_id' => $item->product_id,
                    'store_id' => $transfer->from_store_id,
                    'quantity' => -$item->quantity,
                    'source_type' => StockTransfer::class,
                    'source_id' => $transfer->id,
                    'batch_number' => $item->batch_number,
                    'notes' => 'Transfer out: '.$transfer->reference,
                    'created_by' => $userId,
                ]);

                // Add to destination store (create new layer)
                $this->createInventoryLayer->handle(
                    new \App\Data\Inventory\CreateInventoryLayerData(
                        product_id: $item->product_id,
                        store_id: $transfer->to_store_id,
                        batch_number: $item->batch_number,
                        expiry_date: null,
                        unit_cost: 0, // Transfer doesn't change cost
                        received_qty: $item->quantity,
                        remaining_qty: $item->quantity,
                        received_at: now()->toDateTimeString(),
                    )
                );

                // Create stock movement (incoming to destination)
                StockMovement::query()->create([
                    'product_id' => $item->product_id,
                    'store_id' => $transfer->to_store_id,
                    'quantity' => $item->quantity,
                    'source_type' => StockTransfer::class,
                    'source_id' => $transfer->id,
                    'batch_number' => $item->batch_number,
                    'notes' => 'Transfer in: '.$transfer->reference,
                    'created_by' => $userId,
                ]);
            }

            $transfer->update([
                'status' => StockTransferStatusEnum::COMPLETED,
                'updated_by' => $userId,
            ]);

            return $transfer;
        });
    }
}
