<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use App\Data\Batch\UpdateBatchData;
use App\Models\Batch;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;
use Throwable;

final readonly class UpdateBatch
{
    /**
     * @throws Throwable
     */
    public function handle(Batch $batch, UpdateBatchData $data): void
    {
        DB::transaction(static function () use ($batch, $data): void {
            $updateData = [];

            if (! $data->batch_number instanceof Optional) {
                $updateData['batch_number'] = $data->batch_number;
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
        });
    }
}
