<?php

declare(strict_types=1);

namespace App\Actions\Stock;

use App\Enums\StockMovementTypeEnum;
use App\Exceptions\InsufficientStockException;
use App\Models\Batch;
use Illuminate\Database\Eloquent\Model;

final readonly class DeductStock
{
    public function __construct(
        private RecordStockMovement $recorder,
    ) {}

    /**
     * @throws InsufficientStockException
     */
    public function handle(
        Batch $batch,
        int $quantity,
        Model $reference,
        ?string $note = null,
    ): Batch {
        $batch = Batch::query()
            ->lockForUpdate()
            ->findOrFail($batch->id);

        if ($batch->quantity < $quantity) {
            throw new InsufficientStockException(
                required: $quantity,
                available: $batch->quantity,
                batchId: $batch->id,
                productName: $batch->product->name,
            );
        }

        $previousQuantity = $batch->quantity;
        $batch->decrement('quantity', $quantity);

        $this->recorder->handle(
            batch: $batch->refresh(),
            type: StockMovementTypeEnum::Out,
            quantity: -$quantity, // stored as negative
            reference: $reference,
            previousQuantity: $previousQuantity,
            note: $note,
        );

        return $batch->refresh();
    }
}
