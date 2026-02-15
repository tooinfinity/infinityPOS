<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use App\Models\Batch;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdateBatch
{
    /**
     * @param  array{batch_number?: string|null, cost_amount?: int, quantity?: int, expires_at?: DateTimeInterface|string|null}  $data
     *
     * @throws Throwable
     */
    public function handle(Batch $batch, array $data): void
    {
        DB::transaction(static function () use ($batch, $data): void {
            $batch->update($data);
        });
    }
}
