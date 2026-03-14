<?php

declare(strict_types=1);

namespace App\Builders;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends Builder<Warehouse>
 */
final class WarehouseBuilder extends Builder
{
    public function search(?string $search): self
    {
        return $this->when($search, fn (self $q): self => $q->where(fn (self $q): self => $q
            ->where('name', 'like', "%$search%")
            ->orWhere('code', 'like', "%$search%")
            ->orWhere('city', 'like', "%$search%")
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
     * @return LengthAwarePaginator<int, Warehouse>
     */
    public function paginateWithFilters(array $filters, ?int $perPage = null): LengthAwarePaginator
    {
        return $this
            ->applyFilters($filters)
            ->paginate($perPage ?? 25)
            ->withQueryString();
    }
}
