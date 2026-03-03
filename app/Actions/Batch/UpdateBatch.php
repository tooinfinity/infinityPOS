<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use App\Actions\StockMovement\RecordStockMovement;
use App\Data\Batch\UpdateBatchData;
use App\Data\StockMovement\RecordStockMovementData;
use App\Enums\StockMovementTypeEnum;
use App\Models\Batch;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;
use Throwable;

final readonly class UpdateBatch
{
    public function __construct(
        private RecordStockMovement $recordStockMovement,
        private BatchNumberGenerator $generator,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(Batch $batch, UpdateBatchData $data, ?string $note = null): Batch
    {
        $recordStockMovement = $this->recordStockMovement;
        $generator = $this->generator;

        return DB::transaction(static function () use ($batch, $data, $note, $recordStockMovement, $generator): Batch {
            $previousQuantity = $batch->quantity;
            $updateData = [];

            if (! $data->batch_number instanceof Optional) {
                $updateData['batch_number'] = $data->batch_number ?? $generator->handle($batch->product_id);
            }
            if (! $data->cost_amount instanceof Optional) {
                $updateData['cost_amount'] = $data->cost_amount;
            }
            if (! $data->quantity instanceof Optional) {
                $updateData['quantity'] = $data->quantity;
            }
            if (! $data->expires_at instanceof Optional) {
                $updateData['expires_at'] = $data->expires_at;
            }

            $batch->update($updateData);
            $batch = $batch->refresh();

            if (! $data->quantity instanceof Optional && $data->quantity !== $previousQuantity) {
                $quantityDifference = $data->quantity - $previousQuantity;
                $recordStockMovement->handle(new RecordStockMovementData(
                    warehouse_id: $batch->warehouse_id,
                    product_id: $batch->product_id,
                    type: StockMovementTypeEnum::Adjustment,
                    quantity: $quantityDifference,
                    previous_quantity: $previousQuantity,
                    current_quantity: $data->quantity,
                    reference_type: Batch::class,
                    reference_id: $batch->id,
                    batch_id: $batch->id,
                    user_id: null,
                    note: $note ?? 'Batch quantity adjustment',
                ));
            }

            return $batch;
        });
    }
}
