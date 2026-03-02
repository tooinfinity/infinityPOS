<?php

declare(strict_types=1);

namespace App\Actions\Stock;

use App\Enums\StockMovementTypeEnum;
use App\Models\Batch;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DeductSaleStock
{
    public function __construct(
        private AdjustBatchQuantity $adjustBatchQuantity,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(Sale $sale, string $note): void
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

        DB::transaction(function () use ($sale, $batchIds, $note): void {
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

                $this->adjustBatchQuantity->handle(
                    $batch,
                    -$item->quantity,
                    StockMovementTypeEnum::Out,
                    $sale,
                    $note,
                    $sale->user_id,
                );
            }
        });
    }
}
