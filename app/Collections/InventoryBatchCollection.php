<?php

declare(strict_types=1);

namespace App\Collections;

use App\Models\InventoryBatch;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends Collection<int, InventoryBatch>
 */
final class InventoryBatchCollection extends Collection
{
    /**
     * Get batches with remaining quantity (FIFO eligible).
     *
     * @return self<int, InventoryBatch>
     */
    public function available(): self
    {
        return $this->filter(fn (InventoryBatch $batch): bool => $batch->quantity_remaining > 0);
    }

    /**
     * Get batches ordered by FIFO (oldest first).
     *
     * @return self<int, InventoryBatch>
     */
    public function fifoOrder(): self
    {
        return $this->sortBy('batch_date')->values();
    }

    /**
     * Calculate total available quantity.
     */
    public function totalAvailable(): int
    {
        $sum = $this->sum('quantity_remaining');

        return is_numeric($sum) ? (int) $sum : 0;
    }

    /**
     * Calculate weighted average cost.
     */
    public function averageCost(): int
    {
        $totalQuantity = $this->sum('quantity_remaining');
        $totalQuantity = is_numeric($totalQuantity) ? (int) $totalQuantity : 0;

        if ($totalQuantity === 0) {
            return 0;
        }

        $totalValue = (int) $this->sum(fn (InventoryBatch $batch): int => $batch->quantity_remaining * $batch->unit_cost);

        return (int) round($totalValue / $totalQuantity);
    }

    /**
     * Calculate total inventory value.
     */
    public function totalValue(): int
    {
        return (int) $this->sum(fn (InventoryBatch $batch): int => $batch->quantity_remaining * $batch->unit_cost);
    }
}
