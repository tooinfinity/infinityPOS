<?php

declare(strict_types=1);

namespace App\QueryBuilders;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<Expense>
 */
final class ExpenseQueryBuilder extends Builder
{
    public function inCategory(int $categoryId): self
    {
        return $this->where('category_id', $categoryId);
    }

    public function inStore(int $storeId): self
    {
        return $this->where('store_id', $storeId);
    }

    public function byUser(int $userId): self
    {
        return $this->where('user_id', $userId);
    }

    public function fromMoneybox(int $moneyboxId): self
    {
        return $this->where('moneybox_id', $moneyboxId);
    }

    public function withCategory(): self
    {
        return $this->with('category');
    }

    public function withStore(): self
    {
        return $this->with('store');
    }

    public function withUser(): self
    {
        return $this->with('user');
    }

    public function withMoneybox(): self
    {
        return $this->with('moneybox');
    }

    public function withAllRelations(): self
    {
        return $this->with(['category', 'store', 'user', 'moneybox']);
    }

    public function betweenDates(Carbon $startDate, Carbon $endDate): self
    {
        return $this->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function today(): self
    {
        return $this->whereDate('created_at', now()->toDateString());
    }

    public function thisWeek(): self
    {
        return $this->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function thisMonth(): self
    {
        return $this->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);
    }

    public function thisYear(): self
    {
        return $this->whereYear('created_at', now()->year);
    }

    public function lastMonth(): self
    {
        return $this->whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month);
    }

    public function amountGreaterThan(float $amount): self
    {
        return $this->where('amount', '>', $amount);
    }

    public function amountLessThan(float $amount): self
    {
        return $this->where('amount', '<', $amount);
    }

    public function amountBetween(float $min, float $max): self
    {
        return $this->whereBetween('amount', [$min, $max]);
    }

    public function searchByDescription(string $search): self
    {
        return $this->where('description', 'like', sprintf('%%%s%%', $search));
    }

    public function orderByAmount(string $direction = 'desc'): self
    {
        return $this->orderBy('amount', $direction);
    }

    public function orderByDate(string $direction = 'desc'): self
    {
        return $this->orderBy('created_at', $direction);
    }

    public function recent(int $limit = 10): self
    {
        return $this->latest()
            ->limit($limit);
    }

    public function largest(int $limit = 10): self
    {
        return $this->orderBy('amount', 'desc')
            ->limit($limit);
    }
}
