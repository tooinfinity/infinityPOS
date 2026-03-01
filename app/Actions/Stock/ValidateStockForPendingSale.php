<?php

declare(strict_types=1);

namespace App\Actions\Stock;

use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidBatchException;
use App\Models\Batch;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class ValidateStockForPendingSale
{
    /**
     * @throws Throwable
     */
    public function handle(Sale $sale, int $batchId, int $quantity, ?int $excludeItemId = null, ?int $productId = null): void
    {
        /** @var Batch|null $batch */
        $batch = DB::table('batches')
            ->lockForUpdate()
            ->find($batchId);

        throw_if($batch === null, InvalidBatchException::class, $batchId, 'not found');

        throw_if($productId !== null && $batch->product_id !== $productId, InvalidBatchException::class, $batchId, "does not belong to product $productId");

        if ($batch->warehouse_id !== $sale->warehouse_id) {
            throw new InvalidBatchException($batchId, "not in warehouse $sale->warehouse_id");
        }

        /** @var int $existingQuantity */
        $existingQuantity = $sale->items()
            ->where('batch_id', $batchId)
            ->when($excludeItemId !== null, fn (Builder $q) => $q->where('id', '!=', $excludeItemId))
            ->sum('quantity');

        $totalRequired = $existingQuantity + $quantity;
        $available = $batch->quantity - $existingQuantity;

        throw_if($totalRequired > $batch->quantity, InsufficientStockException::class, $quantity, $available, $batchId);
    }
}
