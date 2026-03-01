<?php

declare(strict_types=1);

namespace App\Actions\Stock;

use App\Data\Sale\SaleItemData;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidBatchException;
use App\Models\Batch;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\DataCollection;
use Throwable;

final readonly class ValidateStockForNewSale
{
    /**
     * @param  DataCollection<int, SaleItemData>  $items
     *
     * @throws Throwable
     */
    public function handle(DataCollection $items, int $warehouseId): void
    {
        $itemsArray = $items->toArray();
        $batchIds = array_unique(array_column($itemsArray, 'batch_id'));

        /** @var Collection<int, Batch> $batches */
        $batches = Batch::query()
            ->lockForUpdate()
            ->whereIn('id', $batchIds)
            ->get()
            ->keyBy('id');

        $requiredByBatch = $this->calculateRequiredQuantities($items);

        $this->validateBatchesExist($items, $batches);
        $this->validateBatchesBelongToWarehouse($items, $batches, $warehouseId);
        $this->validateStockAvailability($requiredByBatch, $batches);
    }

    /**
     * @param  DataCollection<int, SaleItemData>  $items
     * @return array<int, array{quantity: int, product_id: int}>
     */
    private function calculateRequiredQuantities(DataCollection $items): array
    {
        $requiredByBatch = [];
        foreach ($items as $item) {
            if (! isset($requiredByBatch[$item->batch_id])) {
                $requiredByBatch[$item->batch_id] = [
                    'quantity' => 0,
                    'product_id' => $item->product_id,
                ];
            }
            $requiredByBatch[$item->batch_id]['quantity'] += $item->quantity;
        }

        return $requiredByBatch;
    }

    /**
     * @param  DataCollection<int, SaleItemData>  $items
     * @param  Collection<int, Batch>  $batches
     *
     * @throws Throwable
     */
    private function validateBatchesExist(DataCollection $items, Collection $batches): void
    {
        foreach ($items as $item) {
            if (! $batches->has($item->batch_id)) {
                throw new InvalidBatchException($item->batch_id, 'not found');
            }
        }
    }

    /**
     * @param  DataCollection<int, SaleItemData>  $items
     * @param  Collection<int, Batch>  $batches
     *
     * @throws Throwable
     */
    private function validateBatchesBelongToWarehouse(DataCollection $items, Collection $batches, int $warehouseId): void
    {
        foreach ($items as $item) {
            /** @var Batch $batch */
            $batch = $batches->get($item->batch_id);

            if ($batch->product_id !== $item->product_id) {
                throw new InvalidBatchException($item->batch_id, "does not belong to product $item->product_id");
            }
            if ($batch->warehouse_id !== $warehouseId) {
                throw new InvalidBatchException($item->batch_id, "not in warehouse $warehouseId");
            }
        }
    }

    /**
     * @param  array<int, array{quantity: int, product_id: int}>  $requiredByBatch
     * @param  Collection<int, Batch>  $batches
     *
     * @throws Throwable
     */
    private function validateStockAvailability(array $requiredByBatch, Collection $batches): void
    {
        foreach ($requiredByBatch as $batchId => $required) {
            /** @var Batch $batch */
            $batch = $batches->get($batchId);

            if ($batch->quantity < $required['quantity']) {
                throw new InsufficientStockException($required['quantity'], $batch->quantity, $batchId);
            }
        }
    }
}
