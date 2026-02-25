<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use App\Data\Batch\CreateBatchData;
use App\Models\Batch;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateBatch
{
    /**
     * @throws Throwable
     */
    public function handle(CreateBatchData $data): Batch
    {
        return DB::transaction(static fn (): Batch => Batch::query()->forceCreate([
            'product_id' => $data->product_id,
            'warehouse_id' => $data->warehouse_id,
            'batch_number' => $data->batch_number ?? 'BAT-'.now()->getTimestampMs().'-'.random_int(1000, 9999),
            'cost_amount' => $data->cost_amount,
            'quantity' => $data->quantity,
            'expires_at' => $data->expires_at,
        ]))->refresh();
    }
}
