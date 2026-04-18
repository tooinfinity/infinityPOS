<?php

declare(strict_types=1);

namespace App\Actions\Stock;

use App\Enums\StockMovementTypeEnum;
use App\Exceptions\InvalidOperationException;
use App\Models\Batch;
use Illuminate\Database\Eloquent\Model;
use Throwable;

final readonly class AddStock
{
    public function __construct(
        private RecordStockMovement $recorder,
    ) {}

    /**
     * @throws InvalidOperationException
     * @throws Throwable
     */
    public function handle(
        Batch $batch,
        int $quantity,
        Model $reference,
        ?string $note = null,
    ): Batch {
        throw_if($quantity <= 0, InvalidOperationException::class, 'add', 'Stock', 'Quantity must be positive.');

        $batch = Batch::query()
            ->lockForUpdate()
            ->findOrFail($batch->id);

        $previousQuantity = $batch->quantity;
        $batch->increment('quantity', $quantity);

        $this->recorder->handle(
            batch: $batch->refresh(),
            type: StockMovementTypeEnum::In,
            quantity: $quantity, // stored as positive
            reference: $reference,
            previousQuantity: $previousQuantity,
            note: $note,
        );

        return $batch->refresh();
    }
}
