<?php

declare(strict_types=1);

namespace App\Actions\Stock;

use App\Enums\StockMovementTypeEnum;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidOperationException;
use App\Models\Batch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DeductStock
{
    public function __construct(
        private RecordStockMovement $recorder,
    ) {}

    /**
     * @throws InsufficientStockException
     * @throws InvalidOperationException|Throwable
     */
    public function handle(
        Batch $batch,
        int $quantity,
        Model $reference,
        ?string $note = null,
    ): Batch {
        throw_if($quantity <= 0, InvalidOperationException::class, 'deduct', 'Stock', 'Quantity must be positive.');

        $updated = DB::table('batches')
            ->where('id', $batch->id)
            ->where('quantity', '>=', $quantity)
            ->decrement('quantity', $quantity);

        if ($updated === 0) {
            $batch = Batch::query()->findOrFail($batch->id);
            throw new InsufficientStockException(
                required: $quantity,
                available: $batch->quantity,
                batchId: $batch->id,
                productName: $batch->product->name,
            );
        }
        /** @var Batch $batch */
        $batch = $batch->fresh();
        $previousQuantity = $batch->quantity + $quantity;

        $this->recorder->handle(
            batch: $batch,
            type: StockMovementTypeEnum::Out,
            quantity: -$quantity,
            reference: $reference,
            previousQuantity: $previousQuantity,
            note: $note,
        );

        return $batch;
    }
}
