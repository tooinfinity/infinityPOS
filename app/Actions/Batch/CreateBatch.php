<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use App\Models\Batch;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateBatch
{
    /**
     * @param  array{product_id: int, warehouse_id: int, batch_number?: string|null, cost_amount: int, quantity: int, expires_at?: DateTimeInterface|string|null}  $data
     *
     * @throws Throwable
     */
    public function handle(array $data): Batch
    {
        return DB::transaction(static fn (): Batch => Batch::query()->create($data));
    }
}
