<?php

declare(strict_types=1);

namespace App\Actions\Stock;

use App\Actions\StockMovement\RecordStockMovement;
use App\Data\StockMovement\RecordStockMovementData;
use App\Enums\StockMovementTypeEnum;
use App\Exceptions\InsufficientStockException;
use App\Models\Batch;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DeductSaleStock
{
    public function __construct(
        private RecordStockMovement $recordStockMovement,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(Sale $sale, string $note, bool $validateAvailability = false): void
    {
        $batchIds = $sale->items
            ->pluck('batch_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($batchIds === []) {
            return;
        }

        DB::transaction(function () use ($sale, $batchIds, $note, $validateAvailability): void {
            /** @var Collection<int, Batch> $batches */
            $batches = Batch::query()
                ->lockForUpdate()
                ->whereIn('id', $batchIds)
                ->get()
                ->keyBy('id');

            if ($validateAvailability) {
                $this->validateStockAvailability($sale, $batches);
            }

            foreach ($sale->items as $item) {
                if ($item->batch_id === null) {
                    continue;
                }

                /** @var Batch $batch */
                $batch = $batches->get($item->batch_id);

                $previousQuantity = $batch->quantity;
                $newQuantity = $previousQuantity - $item->quantity;

                $batch->forceFill(['quantity' => $newQuantity])->save();

                $this->recordStockMovement->handle(new RecordStockMovementData(
                    warehouse_id: $sale->warehouse_id,
                    product_id: $item->product_id,
                    type: StockMovementTypeEnum::Out,
                    quantity: $item->quantity,
                    previous_quantity: $previousQuantity,
                    current_quantity: $newQuantity,
                    reference_type: Sale::class,
                    reference_id: $sale->id,
                    batch_id: $batch->id,
                    user_id: $sale->user_id,
                    note: $note,
                ));
            }
        });
    }

    /**
     * @param  Collection<int, Batch>  $batches
     *
     * @throws InsufficientStockException
     */
    private function validateStockAvailability(Sale $sale, Collection $batches): void
    {
        /** @var array<int, int> $requiredQuantities */
        $requiredQuantities = [];

        foreach ($sale->items as $item) {
            if ($item->batch_id === null) {
                continue;
            }

            $requiredQuantities[$item->batch_id] = ($requiredQuantities[$item->batch_id] ?? 0) + $item->quantity;
        }

        foreach ($requiredQuantities as $batchId => $requiredQuantity) {
            /** @var Batch $batch */
            $batch = $batches->get($batchId);

            $newQuantity = $batch->quantity - $requiredQuantity;

            if ($newQuantity < 0) {
                throw new InsufficientStockException(
                    required: $requiredQuantity,
                    available: $batch->quantity,
                    batchId: $batchId
                );
            }
        }
    }
}
