<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Actions\StockMovement\RecordStockMovement;
use App\Data\StockMovement\RecordStockMovementData;
use App\Enums\StockMovementTypeEnum;
use App\Enums\StockTransferStatusEnum;
use App\Models\Batch;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class CompleteStockTransfer
{
    public function __construct(private RecordStockMovement $recordStockMovement) {}

    /**
     * @throws Throwable
     */
    public function handle(StockTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer): void {
            throw_if($transfer->status !== StockTransferStatusEnum::Pending, RuntimeException::class, 'Only pending transfers can be completed.');

            $this->validateSufficientStock($transfer);

            foreach ($transfer->items as $item) {
                $this->processItem($transfer, $item);
            }

            $transfer->forceFill(['status' => StockTransferStatusEnum::Completed])->save();
        });
    }

    private function validateSufficientStock(StockTransfer $transfer): void
    {
        foreach ($transfer->items as $item) {
            if ($item->batch === null) {
                continue;
            }
            if ($item->batch->quantity >= $item->quantity) {
                continue;
            }
            throw new RuntimeException(
                sprintf(
                    'Insufficient stock in batch. Required: %d, Available: %d',
                    $item->quantity,
                    $item->batch->quantity
                )
            );
        }
    }

    private function processItem(StockTransfer $transfer, StockTransferItem $item): void
    {
        $sourceBatch = $item->batch;
        $previousQuantity = $sourceBatch !== null ? $sourceBatch->quantity : 0;

        // Decrease source batch quantity
        if ($sourceBatch !== null) {
            $sourceBatch->decrement('quantity', $item->quantity);
        }

        // Create destination batch
        $destinationBatch = new Batch();
        $destinationBatch->forceFill([
            'product_id' => $item->product_id,
            'warehouse_id' => $transfer->to_warehouse_id,
            'batch_number' => $sourceBatch?->batch_number,
            'cost_amount' => $sourceBatch !== null ? $sourceBatch->cost_amount : 0,
            'quantity' => $item->quantity,
            'expires_at' => $sourceBatch?->expires_at,
        ])->save();

        // Record stock movement out from source
        $this->recordStockMovement->handle(new RecordStockMovementData(
            warehouse_id: $transfer->from_warehouse_id,
            product_id: (int) $item->product_id,
            type: StockMovementTypeEnum::Transfer,
            quantity: (int) $item->quantity,
            previous_quantity: (int) $previousQuantity,
            current_quantity: (int) $previousQuantity - (int) $item->quantity,
            reference_type: StockTransfer::class,
            reference_id: $transfer->id,
            batch_id: $sourceBatch?->id,
            user_id: $transfer->user_id,
            note: 'Stock transfer out',
            created_at: null,
        ));

        // Record stock movement in to destination
        $this->recordStockMovement->handle(new RecordStockMovementData(
            warehouse_id: $transfer->to_warehouse_id,
            product_id: (int) $item->product_id,
            type: StockMovementTypeEnum::Transfer,
            quantity: (int) $item->quantity,
            previous_quantity: 0,
            current_quantity: (int) $item->quantity,
            reference_type: StockTransfer::class,
            reference_id: $transfer->id,
            batch_id: $destinationBatch->id,
            user_id: $transfer->user_id,
            note: 'Stock transfer in',
            created_at: null,
        ));
    }
}
