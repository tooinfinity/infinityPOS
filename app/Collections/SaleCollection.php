<?php

declare(strict_types=1);

namespace App\Collections;

use App\Enums\SaleStatusEnum;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends Collection<int, Sale>
 */
final class SaleCollection extends Collection
{
    /**
     * Calculate total profit from all sales.
     */
    public function totalProfit(): int
    {
        return (int) $this->sum(function (Sale $sale): int {
            $itemSum = $sale->items->sum('profit');

            return is_numeric($itemSum) ? (int) $itemSum : 0;
        });
    }

    /**
     * Calculate total revenue.
     */
    public function totalRevenue(): int
    {
        $sum = $this->sum('total_amount');

        return is_numeric($sum) ? (int) $sum : 0;
    }

    /**
     * Get sales by payment method.
     *
     * @return \Illuminate\Support\Collection<string, array{count: int, total: int}>
     */
    public function byPaymentMethod(): \Illuminate\Support\Collection
    {
        /** @var \Illuminate\Support\Collection<string, self> $grouped */
        $grouped = $this->groupBy('payment_method');

        return $grouped->map(function (self $sales): array {
            $sum = $sales->sum('total_amount');
            $total = is_numeric($sum) ? (int) $sum : 0;

            return [
                'count' => $sales->count(),
                'total' => $total,
            ];
        });
    }

    /**
     * Get only completed sales.
     *
     * @return self<int, Sale>
     */
    public function completed(): self
    {
        return $this->filter(fn (Sale $sale): bool => $sale->status === SaleStatusEnum::COMPLETED);
    }

    /**
     * Calculate average sale amount.
     */
    public function averageSaleAmount(): float
    {
        if ($this->isEmpty()) {
            return 0;
        }

        $sum = $this->sum('total_amount');
        $total = is_numeric($sum) ? (int) $sum : 0;

        return $total / $this->count();
    }
}
