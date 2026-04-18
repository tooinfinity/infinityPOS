<?php

declare(strict_types=1);

namespace App\Actions\Stock;

use App\Actions\Batch\FindOrCreateBatch;
use App\Enums\StockMovementTypeEnum;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\WarehouseSameException;
use App\Models\Batch;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class TransferStock
{
    public function __construct(
        private RecordStockMovement $recorder,
        private FindOrCreateBatch $findOrCreateBatch,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(
        Batch $sourceBatch,
        int $destinationWarehouseId,
        int $quantity,
        StockTransfer $transfer,
    ): TransferResult {
        throw_if($sourceBatch->warehouse_id === $destinationWarehouseId, WarehouseSameException::class);

        /** @var TransferResult $result */
        $result = DB::transaction(function () use (
            $sourceBatch,
            $destinationWarehouseId,
            $quantity,
            $transfer,
        ): TransferResult {
            $updated = DB::table('batches')
                ->where('id', $sourceBatch->id)
                ->where('quantity', '>=', $quantity)
                ->decrement('quantity', $quantity);

            if ($updated === 0) {
                $source = Batch::query()->findOrFail($sourceBatch->id);
                throw new InsufficientStockException(
                    required: $quantity,
                    available: $source->quantity,
                    batchId: $source->id,
                    productName: $source->product->name,
                );
            }
            /** @var Batch $source */
            $source = $sourceBatch->fresh();
            $previousSourceQuantity = $source->quantity + $quantity;

            $this->recorder->handle(
                batch: $source,
                type: StockMovementTypeEnum::Transfer,
                quantity: -$quantity,
                reference: $transfer,
                previousQuantity: $previousSourceQuantity,
                note: sprintf(
                    'Transfer %s → warehouse #%d',
                    $transfer->reference_no,
                    $destinationWarehouseId,
                ),
            );

            $destination = $this->findOrCreateBatch->handle(
                productId: $source->product_id,
                warehouseId: $destinationWarehouseId,
                costAmount: $source->cost_amount,
                expiresAt: $source->expires_at,
            );

            $destination = Batch::query()
                ->lockForUpdate()
                ->findOrFail($destination->id);

            $previousDestinationQuantity = $destination->quantity;
            $destination->increment('quantity', $quantity);

            $this->recorder->handle(
                batch: $destination->refresh(),
                type: StockMovementTypeEnum::Transfer,
                quantity: $quantity,
                reference: $transfer,
                previousQuantity: $previousDestinationQuantity,
                note: sprintf(
                    'Transfer %s ← warehouse #%d',
                    $transfer->reference_no,
                    $source->warehouse_id,
                ),
            );

            return new TransferResult(
                source: $source->refresh(),
                destination: $destination->refresh(),
            );
        });

        return $result;
    }
}
