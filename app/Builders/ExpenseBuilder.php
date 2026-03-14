<?php

declare(strict_types=1);

namespace App\Builders;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends Builder<Expense>
 */
final class ExpenseBuilder extends Builder
{
    public function search(?string $search): self
    {
        return $this->when($search, fn (self $q): self => $q->where(fn (self $q): self => $q
            ->where('reference_no', 'like', "%$search%")
            ->orWhere('description', 'like', "%$search%")
        ));
    }

    public function recent(int $days = 30): self
    {
        return $this->where('expense_date', '>=', now()->subDays($days));
    }

    public function today(): self
    {
        return $this->whereDate('expense_date', today());
    }

    /**
     * @param array{
     *     search?: string|null,
     *     sort?: string|null,
     *     direction?: 'asc'|'desc'|string|null
     * } $filters
     */
    public function applyFilters(array $filters): self
    {
        return $this
            ->search($filters['search'] ?? null)
            ->when(
                $filters['sort'] ?? null,
                fn (self $q, string $col): self => $q->orderBy($col, $filters['direction'] ?? 'asc'),
                fn (self $q): self => $q->latest()
            );
    }

    /**
     * @param array{
     *     search?: string|null,
     *     sort?: string|null,
     *     direction?: 'asc'|'desc'|string|null
     * } $filters
     * @return LengthAwarePaginator<int, Expense>
     */
    public function paginateWithFilters(array $filters, ?int $perPage = null): LengthAwarePaginator
    {
        return $this
            ->applyFilters($filters)
            ->with(['expenseCategory'])
            ->paginate($perPage ?? 25)
            ->withQueryString();
    }
}
