<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use App\Models\Batch;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class FindOrCreateBatch
{
    public function __construct(private BatchNumberGenerator $generator) {}

    /**
     * @throws Throwable
     */
    public function handle(
        int $productId,
        int $warehouseId,
        int $costAmount,
        ?CarbonInterface $expiresAt = null,
    ): Batch {
        return DB::transaction(function () use ($productId, $warehouseId, $costAmount, $expiresAt): Batch {
            $existingBatch = Batch::query()
                ->lockForUpdate()
                ->matching($productId, $warehouseId, $costAmount, $expiresAt)
                ->first();

            return $existingBatch ?? Batch::query()->forceCreate([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'batch_number' => $this->generator->handle($productId),
                'cost_amount' => $costAmount,
                'quantity' => 0,
                'expires_at' => $expiresAt,
            ]);
        });
    }
}
