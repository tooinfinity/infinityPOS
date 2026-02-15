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
        return DB::transaction(static fn (): Batch => Batch::query()->create([
            'product_id' => $data->product_id,
            'warehouse_id' => $data->warehouse_id,
            'batch_number' => $data->batch_number,
            'cost_amount' => $data->cost_amount,
            'quantity' => $data->quantity,
            'expires_at' => $data->expires_at,
        ]));
    }
}
