<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use App\Data\Batch\BatchData;
use App\Models\Batch;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdateBatch
{
    public function __construct(
        private BatchNumberGenerator $generator,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(Batch $batch, BatchData $data): Batch
    {

        return DB::transaction(function () use ($batch, $data): Batch {
            $batch->update([
                'product_id' => $data->product_id ?? $batch->product_id,
                'warehouse_id' => $data->warehouse_id ?? $batch->warehouse_id,
                'batch_number' => $data->batch_number ?? $this->generator->handle($data->product_id ?? $batch->product_id),
                'cost_amount' => $data->cost_amount ?? $batch->cost_amount,
                'quantity' => $data->quantity ?? $batch->quantity,
                'expires_at' => $data->expires_at ?? $batch->expires_at,
            ]);

            // Record stock movement if quantity has changed
            return $batch->refresh();
        });
    }
}
