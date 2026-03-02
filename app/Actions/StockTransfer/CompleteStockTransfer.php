<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Actions\Batch\FindOrCreateBatch;
use App\Actions\Shared\ValidateStatusIsPending;
use App\Actions\StockMovement\RecordStockMovement;
use App\Data\StockMovement\RecordStockMovementData;
use App\Enums\StockMovementTypeEnum;
use App\Enums\StockTransferStatusEnum;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidOperationException;
use App\Models\Batch;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CompleteStockTransfer
{
    public function __construct(
        private RecordStockMovement $recordStockMovement,
        private ValidateStatusIsPending $validateStatus,
        private FindOrCreateBatch $findOrCreateBatch,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(StockTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer): void {
            /** @var StockTransfer $transfer */
            $transfer = StockTransfer::query()
                ->lockForUpdate()
                ->with([
                    'items.product',
                    'items.batch' => fn (Relation $query): Relation => $query->lockForUpdate(),
                ])
                ->findOrFail($transfer->id);

            $this->validateStatus->validateTransition(
                $transfer->status,
                StockTransferStatusEnum::Completed,
                'StockTransfer'
            );

            $this->validateSufficientStock($transfer);

            foreach ($transfer->items as $item) {
                $this->processItem($transfer, $item);
            }

            $transfer->forceFill(['status' => StockTransferStatusEnum::Completed])->save();
        });
    }

    /**
     * @throws InsufficientStockException
     * @throws InvalidOperationException
     */
    private function validateSufficientStock(StockTransfer $transfer): void
    {
        foreach ($transfer->items as $item) {
            if ($item->batch === null) {
                throw new InvalidOperationException(
                    'complete',
                    'StockTransfer',
                    sprintf('Stock transfer item for product %d is missing a source batch.', $item->product_id)
                );
            }

            if ($item->batch->quantity < $item->quantity) {
                throw new InsufficientStockException(
                    required: $item->quantity,
                    available: $item->batch->quantity,
                    batchId: $item->batch->id
                );
            }
        }
    }

    /**
     * @throws Throwable
     */
    private function processItem(StockTransfer $transfer, StockTransferItem $item): void
    {
        /** @var Batch $sourceBatch */
        $sourceBatch = $item->batch;

        $previousQuantity = $sourceBatch->quantity;
        $sourceBatch->forceFill(['quantity' => $sourceBatch->quantity - $item->quantity])->save();

        $destinationBatch = $this->findOrCreateBatch->handle(
            $sourceBatch->product_id,
            $transfer->to_warehouse_id,
            $sourceBatch->cost_amount,
            $sourceBatch->expires_at,
        );

        $this->recordStockMovement->handle(new RecordStockMovementData(
            warehouse_id: $transfer->from_warehouse_id,
            product_id: $item->product_id,
            type: StockMovementTypeEnum::Transfer,
            quantity: $item->quantity,
            previous_quantity: $previousQuantity,
            current_quantity: $previousQuantity - $item->quantity,
            reference_type: StockTransfer::class,
            reference_id: $transfer->id,
            batch_id: $sourceBatch->id,
            user_id: $transfer->user_id,
            note: 'Stock transfer out',
        ));

        $previousDestQuantity = $destinationBatch->quantity;
        $destinationBatch->forceFill(['quantity' => $destinationBatch->quantity + $item->quantity])->save();

        $this->recordStockMovement->handle(new RecordStockMovementData(
            warehouse_id: $transfer->to_warehouse_id,
            product_id: $item->product_id,
            type: StockMovementTypeEnum::Transfer,
            quantity: $item->quantity,
            previous_quantity: $previousDestQuantity,
            current_quantity: $previousDestQuantity + $item->quantity,
            reference_type: StockTransfer::class,
            reference_id: $transfer->id,
            batch_id: $destinationBatch->id,
            user_id: $transfer->user_id,
            note: 'Stock transfer in',
        ));
    }
}
