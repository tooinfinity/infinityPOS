<?php

declare(strict_types=1);

namespace App\Builders;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends Builder<Category>
 */
final class CategoryBuilder extends Builder
{
    public function search(?string $search): self
    {
        return $this->when($search, fn (self $q): self => $q->where(fn (self $q): self => $q
            ->where('name', 'like', "%$search%")
            ->orWhere('description', 'like', "%$search%")
        ));
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
     * @return LengthAwarePaginator<int, Category>
     */
    public function paginateWithFilters(array $filters, ?int $perPage = null): LengthAwarePaginator
    {
        return $this
            ->applyFilters($filters)
            ->withCount('products')
            ->paginate($perPage ?? 25)
            ->withQueryString();
    }
}
