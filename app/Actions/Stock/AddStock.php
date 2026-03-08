<?php

declare(strict_types=1);

namespace App\Actions\Stock;

use App\Enums\StockMovementTypeEnum;
use App\Models\Batch;
use Illuminate\Database\Eloquent\Model;

final readonly class AddStock
{
    public function __construct(
        private RecordStockMovement $recorder,
    ) {}

    public function handle(
        Batch $batch,
        int $quantity,
        Model $reference,
        ?string $note = null,
    ): Batch {
        $batch = Batch::query()
            ->lockForUpdate()
            ->findOrFail($batch->id);

        $batch->increment('quantity', $quantity);

        $this->recorder->handle(
            batch: $batch->refresh(),
            type: StockMovementTypeEnum::In,
            quantity: $quantity, // stored as positive
            reference: $reference,
            note: $note,
        );

        return $batch->refresh();
    }
}
