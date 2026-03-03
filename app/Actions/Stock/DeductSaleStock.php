<?php

declare(strict_types=1);

namespace App\Actions\Stock;

use App\Actions\StockMovement\CreateStockMovement;
use App\Exceptions\InsufficientStockException;
use App\Models\Batch;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DeductSaleStock
{
    public function __construct(
        private CreateStockMovement $createStockMovement,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(Sale $sale): void
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

        DB::transaction(function () use ($sale, $batchIds): void {
            /** @var Collection<int, Batch> $batches */
            $batches = Batch::query()
                ->lockForUpdate()
                ->whereIn('id', $batchIds)
                ->get()
                ->keyBy('id');

            foreach ($sale->items as $item) {
                if ($item->batch_id === null) {
                    continue;
                }

                /** @var Batch $batch */
                $batch = $batches->get($item->batch_id);

                $previousQuantity = $batch->quantity;
                $newQuantity = $previousQuantity - $item->quantity;

                if ($newQuantity < 0) {
                    throw new InsufficientStockException(
                        required: $item->quantity,
                        available: $previousQuantity,
                        batchId: $batch->id,
                    );
                }

                $batch->forceFill(['quantity' => $newQuantity])->save();

                $this->createStockMovement->recordOut(
                    $batch,
                    $item->quantity,
                    $previousQuantity,
                    $sale,
                    $sale->user_id,
                );
            }
        });
    }
}
