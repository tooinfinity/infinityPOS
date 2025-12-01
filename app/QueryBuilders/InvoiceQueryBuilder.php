<?php

declare(strict_types=1);

namespace App\QueryBuilders;

use App\Enums\InvoiceStatusEnum;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<Invoice>
 */
final class InvoiceQueryBuilder extends Builder
{
    public function forClient(int $clientId): self
    {
        return $this->where('client_id', $clientId);
    }

    public function forSale(int $saleId): self
    {
        return $this->where('sale_id', $saleId);
    }

    public function byUser(int $userId): self
    {
        return $this->where('user_id', $userId);
    }

    public function withStatus(InvoiceStatusEnum $status): self
    {
        return $this->where('status', $status);
    }

    public function paid(): self
    {
        return $this->where('status', InvoiceStatusEnum::PAID);
    }

    public function pending(): self
    {
        return $this->where('status', InvoiceStatusEnum::PENDING);
    }

    public function overdue(): self
    {
        return $this->where(function (Builder $q): void {
            $q->where('status', InvoiceStatusEnum::OVERDUE)
                ->orWhere(function (Builder $inner): void {
                    $inner->whereNotIn('status', [InvoiceStatusEnum::PAID, InvoiceStatusEnum::CANCELLED])
                        ->where('due_at', '<', now());
                });
        });
    }

    public function cancelled(): self
    {
        return $this->where('status', InvoiceStatusEnum::CANCELLED);
    }

    public function partiallyPaid(): self
    {
        return $this->where('status', InvoiceStatusEnum::PARTIALLY_PAID);
    }

    public function unpaid(): self
    {
        return $this->whereIn('status', [
            InvoiceStatusEnum::DRAFT,
            InvoiceStatusEnum::PARTIALLY_PAID,
            InvoiceStatusEnum::OVERDUE,
        ]);
    }

    public function withBalance(): self
    {
        return $this->whereRaw('total > paid');
    }

    public function fullyPaid(): self
    {
        return $this->whereRaw('total <= paid');
    }

    public function issuedBetween(Carbon $startDate, Carbon $endDate): self
    {
        return $this->whereBetween('issued_at', [$startDate, $endDate]);
    }

    public function dueBetween(Carbon $startDate, Carbon $endDate): self
    {
        return $this->whereBetween('due_at', [$startDate, $endDate]);
    }

    public function dueToday(): self
    {
        return $this->whereDate('due_at', now()->toDateString());
    }

    public function dueThisWeek(): self
    {
        return $this->whereBetween('due_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function dueThisMonth(): self
    {
        return $this->whereYear('due_at', now()->year)
            ->whereMonth('due_at', now()->month);
    }

    public function dueInDays(int $days): self
    {
        return $this->whereBetween('due_at', [
            now(),
            now()->addDays($days),
        ]);
    }

    public function issuedToday(): self
    {
        return $this->whereDate('issued_at', now()->toDateString());
    }

    public function issuedThisMonth(): self
    {
        return $this->whereYear('issued_at', now()->year)
            ->whereMonth('issued_at', now()->month);
    }

    public function issuedThisYear(): self
    {
        return $this->whereYear('issued_at', now()->year);
    }

    public function totalGreaterThan(float $amount): self
    {
        return $this->where('total', '>', $amount);
    }

    public function totalLessThan(float $amount): self
    {
        return $this->where('total', '<', $amount);
    }

    public function totalBetween(float $min, float $max): self
    {
        return $this->whereBetween('total', [$min, $max]);
    }

    public function searchByReference(string $search): self
    {
        return $this->where('reference', 'like', sprintf('%%%s%%', $search));
    }

    public function withAllRelations(): self
    {
        return $this->with(['sale', 'client', 'user', 'payments']);
    }

    public function withPayments(): self
    {
        return $this->with('payments');
    }

    public function withClient(): self
    {
        return $this->with('client');
    }

    public function orderByIssueDate(string $direction = 'desc'): self
    {
        return $this->orderBy('issued_at', $direction);
    }

    public function orderByDueDate(string $direction = 'asc'): self
    {
        return $this->orderBy('due_at', $direction);
    }

    public function orderByTotal(string $direction = 'desc'): self
    {
        return $this->orderBy('total', $direction);
    }

    public function recent(int $limit = 10): self
    {
        return $this->latest('issued_at')
            ->limit($limit);
    }
}
