<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Actions\Batch\FindOrCreateBatch;
use App\Actions\Shared\ValidateStatusIsPending;
use App\Actions\StockMovement\CreateStockMovement;
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
        private CreateStockMovement $createStockMovement,
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

        $this->createStockMovement->recordTransfer($sourceBatch, $item->quantity, $previousQuantity,
            StockMovementTypeEnum::Out, $transfer, $transfer->user_id);

        $previousDestQuantity = $destinationBatch->quantity;
        $destinationBatch->forceFill(['quantity' => $destinationBatch->quantity + $item->quantity])->save();

        $this->createStockMovement->recordTransfer($destinationBatch, $item->quantity, $previousDestQuantity,
            StockMovementTypeEnum::In, $transfer, $transfer->user_id);
    }
}
