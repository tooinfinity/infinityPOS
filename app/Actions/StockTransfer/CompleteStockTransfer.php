<?php

declare(strict_types=1);

namespace App\Actions\StockTransfer;

use App\Actions\StockMovement\RecordStockMovement;
use App\Data\StockMovement\RecordStockMovementData;
use App\Enums\StockMovementTypeEnum;
use App\Enums\StockTransferStatusEnum;
use App\Exceptions\StateTransitionException;
use App\Models\Batch;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Database\Eloquent\Relations\Relation;
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
            /** @var StockTransfer $transfer */
            $transfer = StockTransfer::query()
                ->lockForUpdate()
                ->with(['items.product', 'items.batch'])
                ->with([
                    'items.product',
                    'items.batch' => fn (Relation $query): Relation => $query->lockForUpdate(),
                ])
                ->findOrFail($transfer->id);

            throw_if(
                ! $transfer->status->canTransitionTo(StockTransferStatusEnum::Completed),
                StateTransitionException::class,
                $transfer->status->label(),
                StockTransferStatusEnum::Completed->label()
            );

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

    /**
     * @throws Throwable
     */
    private function processItem(StockTransfer $transfer, StockTransferItem $item): void
    {
        $sourceBatch = $item->batch;

        if ($sourceBatch === null) {
            $previousQuantity = 0;
        } else {
            $previousQuantity = $sourceBatch->quantity;
            $sourceBatch->forceFill(['quantity' => $sourceBatch->quantity - $item->quantity])->save();
        }

        $destinationBatch = Batch::query()->forceCreate([
            'product_id' => $item->product_id,
            'warehouse_id' => $transfer->to_warehouse_id,
            'batch_number' => $sourceBatch?->batch_number,
            'cost_amount' => $sourceBatch !== null ? $sourceBatch->cost_amount : 0,
            'quantity' => $item->quantity,
            'expires_at' => $sourceBatch?->expires_at,
        ])->refresh();

        $this->recordStockMovement->handle(new RecordStockMovementData(
            warehouse_id: $transfer->from_warehouse_id,
            product_id: $item->product_id,
            type: StockMovementTypeEnum::Transfer,
            quantity: $item->quantity,
            previous_quantity: $previousQuantity,
            current_quantity: $previousQuantity - $item->quantity,
            reference_type: StockTransfer::class,
            reference_id: $transfer->id,
            batch_id: $sourceBatch?->id,
            user_id: $transfer->user_id,
            note: 'Stock transfer out',
            created_at: null,
        ));

        $this->recordStockMovement->handle(new RecordStockMovementData(
            warehouse_id: $transfer->to_warehouse_id,
            product_id: $item->product_id,
            type: StockMovementTypeEnum::Transfer,
            quantity: $item->quantity,
            previous_quantity: 0,
            current_quantity: $item->quantity,
            reference_type: StockTransfer::class,
            reference_id: $transfer->id,
            batch_id: $destinationBatch->id,
            user_id: $transfer->user_id,
            note: 'Stock transfer in',
            created_at: null,
        ));
    }
}
