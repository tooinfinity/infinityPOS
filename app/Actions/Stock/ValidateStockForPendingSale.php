<?php

declare(strict_types=1);

namespace App\Actions\Stock;

use App\Models\Batch;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
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

        throw_if($batch === null, RuntimeException::class, "Batch not found: $batchId");

        if ($productId !== null) {
            throw_if($batch->product_id !== $productId, RuntimeException::class, "Batch does not belong to product {$productId}");
        }

        throw_if($batch->warehouse_id !== $sale->warehouse_id, RuntimeException::class, "Batch is not in the sale's warehouse");

        /** @var int $existingQuantity */
        $existingQuantity = $sale->items()
            ->where('batch_id', $batchId)
            ->when($excludeItemId !== null, fn (Builder $q) => $q->where('id', '!=', $excludeItemId))
            ->sum('quantity');

        $totalRequired = $existingQuantity + $quantity;

        throw_if(
            $totalRequired > $batch->quantity,
            RuntimeException::class,
            "Insufficient stock in batch. Required: {$quantity}, Available: ".($batch->quantity - $existingQuantity)
        );
    }
}
