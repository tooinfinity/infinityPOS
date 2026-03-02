<?php

declare(strict_types=1);

namespace App\Actions\Stock;

use App\Actions\StockMovement\RecordStockMovement;
use App\Data\StockMovement\RecordStockMovementData;
use App\Enums\StockMovementTypeEnum;
use App\Exceptions\InsufficientStockException;
use App\Models\Batch;
use Illuminate\Database\Eloquent\Model;
use Throwable;

final readonly class AdjustBatchQuantity
{
    public function __construct(
        private RecordStockMovement $recordStockMovement,
    ) {}

    /**
     * Adjust batch quantity and record the stock movement.
     *
     * @param  Model  $reference  The entity triggering this adjustment (e.g., Sale, Purchase)
     *
     * @throws InsufficientStockException
     * @throws Throwable
     */
    public function handle(
        Batch $batch,
        int $quantityDelta,
        StockMovementTypeEnum $type,
        Model $reference,
        ?string $note = null,
        ?int $userId = null,
    ): void {
        $previousQuantity = $batch->quantity;
        $newQuantity = $previousQuantity + $quantityDelta;

        if ($newQuantity < 0) {
            throw new InsufficientStockException(
                required: abs($quantityDelta),
                available: $previousQuantity,
                batchId: $batch->id
            );
        }

        $batch->forceFill(['quantity' => $newQuantity])->save();

        // @phpstan-ignore-next-line
        $referenceId = (int) $reference->getKey();

        $this->recordStockMovement->handle(new RecordStockMovementData(
            warehouse_id: $batch->warehouse_id,
            product_id: $batch->product_id,
            type: $type,
            quantity: abs($quantityDelta),
            previous_quantity: $previousQuantity,
            current_quantity: $newQuantity,
            reference_type: $reference::class,
            reference_id: $referenceId,
            batch_id: $batch->id,
            user_id: $userId,
            note: $note,
        ));
    }
}
