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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Random\RandomException;
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
                throw new RuntimeException(
                    sprintf(
                        'Stock transfer item for product %d is missing a source batch.',
                        $item->product_id
                    )
                );
            }

            if ($item->batch->quantity < $item->quantity) {
                throw new RuntimeException(
                    sprintf(
                        'Insufficient stock in batch. Required: %d, Available: %d',
                        $item->quantity,
                        $item->batch->quantity
                    )
                );
            }
        }
    }

    /**
     * @throws Throwable
     */
    private function processItem(StockTransfer $transfer, StockTransferItem $item): void
    {
        $sourceBatch = $item->batch;

        $previousQuantity = $sourceBatch->quantity;
        $sourceBatch->forceFill(['quantity' => $sourceBatch->quantity - $item->quantity])->save();

        $destinationBatch = $this->findOrCreateDestinationBatch($transfer, $sourceBatch);

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

    /**
     * @throws RandomException
     */
    private function findOrCreateDestinationBatch(StockTransfer $transfer, Batch $sourceBatch): Batch
    {
        $expiresAt = $sourceBatch->expires_at;

        $existingBatch = Batch::query()
            ->lockForUpdate()
            ->where('product_id', $sourceBatch->product_id)
            ->where('warehouse_id', $transfer->to_warehouse_id)
            ->where('cost_amount', $sourceBatch->cost_amount)
            ->when(
                $expiresAt !== null,
                fn (Builder $q) => $q->where('expires_at', $expiresAt),
                fn (Builder $q) => $q->whereNull('expires_at'),
            )
            ->first();

        return $existingBatch ?? Batch::query()->forceCreate([
            'product_id' => $sourceBatch->product_id,
            'warehouse_id' => $transfer->to_warehouse_id,
            'batch_number' => 'BAT-'.now()->getTimestampMs().'-'.random_int(1000, 9999),
            'cost_amount' => $sourceBatch->cost_amount,
            'quantity' => 0,
            'expires_at' => $sourceBatch->expires_at,
        ]);
    }
}
