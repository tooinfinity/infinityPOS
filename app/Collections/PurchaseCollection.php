<?php

declare(strict_types=1);

namespace App\Collections;

use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends Collection<int, Purchase>
 */
final class PurchaseCollection extends Collection
{
    /**
     * Calculate total cost of all purchases.
     */
    public function totalCost(): int
    {
        $sum = $this->sum('total_cost');

        return is_numeric($sum) ? (int) $sum : 0;
    }

    /**
     * Calculate total paid amount.
     */
    public function totalPaid(): int
    {
        $sum = $this->sum('paid_amount');

        return is_numeric($sum) ? (int) $sum : 0;
    }

    /**
     * Calculate total outstanding balance.
     */
    public function totalOutstanding(): int
    {
        return $this->sum(fn (Purchase $purchase): int => $purchase->getOutstandingBalance());
    }

    /**
     * Get purchases by payment status.
     *
     * @return self<int, Purchase>
     */
    public function byPaymentStatus(PurchaseStatusEnum $status): self
    {
        return $this->filter(fn (Purchase $purchase): bool => $purchase->payment_status === $status);
    }

    /**
     * Get pending purchases.
     *
     * @return self<int, Purchase>
     */
    public function pending(): self
    {
        return $this->byPaymentStatus(PurchaseStatusEnum::PENDING);
    }

    /**
     * Get completed purchases.
     *
     * @return self<int, Purchase>
     */
    public function completed(): self
    {
        return $this->byPaymentStatus(PurchaseStatusEnum::COMPLETED);
    }
}
