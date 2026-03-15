<?php

declare(strict_types=1);

namespace App\Actions\Stock;

use App\Enums\StockMovementTypeEnum;
use App\Models\Batch;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;

final readonly class RecordStockMovement
{
    public function handle(
        Batch $batch,
        StockMovementTypeEnum $type,
        int $quantity,
        Model $reference,
        int $previousQuantity,
        ?string $note = null,
    ): StockMovement {
        return StockMovement::query()->forceCreate([
            'warehouse_id' => $batch->warehouse_id,
            'product_id' => $batch->product_id,
            'batch_id' => $batch->id,
            'user_id' => auth()->id(),
            'type' => $type,
            'quantity' => $quantity,
            'previous_quantity' => $previousQuantity,
            'current_quantity' => $batch->quantity,
            'reference_type' => $reference->getMorphClass(),
            'reference_id' => $reference->getKey(),
            'note' => $note ?? $type->label(),
        ]);
    }
}
