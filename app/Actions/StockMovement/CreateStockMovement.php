<?php

declare(strict_types=1);

namespace App\Actions\StockMovement;

use App\Data\StockMovement\RecordStockMovementData;
use App\Enums\StockMovementTypeEnum;
use App\Models\Batch;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;

final readonly class CreateStockMovement
{
    public function __construct(
        private RecordStockMovement $recordStockMovement,
    ) {}

    public function recordIn(
        Batch $batch,
        int $quantity,
        int $previousQuantity,
        Model $reference,
        ?int $userId = null,
        ?string $note = null,
    ): StockMovement {
        // @phpstan-ignore-next-line
        $referenceID = (int) $reference->id;

        return $this->recordStockMovement->handle(new RecordStockMovementData(
            warehouse_id: $batch->warehouse_id,
            product_id: $batch->product_id,
            type: StockMovementTypeEnum::In,
            quantity: $quantity,
            previous_quantity: $previousQuantity,
            current_quantity: $previousQuantity + $quantity,
            reference_type: $reference::class,
            reference_id: $referenceID,
            batch_id: $batch->id,
            user_id: $userId,
            note: $note ?? 'Stock received',
        ));
    }

    public function recordOut(
        Batch $batch,
        int $quantity,
        int $previousQuantity,
        Model $reference,
        ?int $userId = null,
        ?string $note = null,
    ): StockMovement {
        // @phpstan-ignore-next-line
        $referenceID = (int) $reference->id;

        return $this->recordStockMovement->handle(new RecordStockMovementData(
            warehouse_id: $batch->warehouse_id,
            product_id: $batch->product_id,
            type: StockMovementTypeEnum::Out,
            quantity: $quantity,
            previous_quantity: $previousQuantity,
            current_quantity: $previousQuantity - $quantity,
            reference_type: $reference::class,
            reference_id: $referenceID,
            batch_id: $batch->id,
            user_id: $userId,
            note: $note ?? 'Stock deducted',
        ));
    }

    public function recordTransfer(
        Batch $batch,
        int $quantity,
        int $previousQuantity,
        StockMovementTypeEnum $direction,
        Model $reference,
        ?int $userId = null,
    ): StockMovement {
        $isOut = $direction === StockMovementTypeEnum::Out;
        // @phpstan-ignore-next-line
        $referenceID = (int) $reference->id;

        return $this->recordStockMovement->handle(new RecordStockMovementData(
            warehouse_id: $batch->warehouse_id,
            product_id: $batch->product_id,
            type: StockMovementTypeEnum::Transfer,
            quantity: $quantity,
            previous_quantity: $previousQuantity,
            current_quantity: $isOut
                ? $previousQuantity - $quantity
                : $previousQuantity + $quantity,
            reference_type: $reference::class,
            reference_id: $referenceID,
            batch_id: $batch->id,
            user_id: $userId,
            note: $isOut ? 'Stock transfer out' : 'Stock transfer in',
        ));
    }
}
