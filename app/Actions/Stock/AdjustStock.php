<?php

declare(strict_types=1);

namespace App\Actions\Stock;

use App\Enums\StockMovementTypeEnum;
use App\Exceptions\InvalidOperationException;
use App\Models\Batch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class AdjustStock
{
    public function __construct(
        private RecordStockMovement $recorder,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(
        Batch $batch,
        int $newQuantity,
        Model $reference,
        ?string $note = null,
    ): Batch {
        if ($newQuantity < 0) {
            throw new InvalidOperationException(
                'adjust',
                'Stock',
                'Adjusted quantity cannot be negative.'
            );
        }

        return DB::transaction(function () use ($batch, $newQuantity, $reference, $note): Batch {
            $batch = Batch::query()
                ->lockForUpdate()
                ->findOrFail($batch->id);

            $difference = $newQuantity - $batch->quantity;

            if ($difference === 0) {
                return $batch;
            }

            $previousQuantity = $batch->quantity;
            $batch->forceFill(['quantity' => $newQuantity])->save();

            $this->recorder->handle(
                batch: $batch->refresh(),
                type: StockMovementTypeEnum::Adjustment,
                quantity: $difference, // positive = stock added, negative = stock removed
                reference: $reference,
                previousQuantity: $previousQuantity,
                note: $note ?? sprintf(
                    'Manual adjustment: %s%d units',
                    $difference > 0 ? '+' : '',
                    $difference,
                ),
            );

            return $batch->refresh();
        });
    }
}
