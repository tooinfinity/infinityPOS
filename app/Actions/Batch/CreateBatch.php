<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use App\Data\Batch\BatchData;
use App\Models\Batch;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateBatch
{
    public function __construct(
        private BatchNumberGenerator $generator,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(BatchData $data): Batch
    {
        return DB::transaction(function () use ($data): Batch {
            return Batch::query()->forceCreate([
                'product_id' => $data->product_id,
                'warehouse_id' => $data->warehouse_id,
                'batch_number' => $data->batch_number ?? $this->generator->handle($data->product_id),
                'cost_amount' => $data->cost_amount,
                'quantity' => $data->quantity,
                'expires_at' => $data->expires_at,
            ])->refresh();

            // Record initial stock movement for the batch
        });
    }
}
