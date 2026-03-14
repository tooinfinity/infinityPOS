<?php

declare(strict_types=1);

namespace App\Builders;

use App\Enums\StockMovementTypeEnum;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends Builder<StockMovement>
 */
final class StockMovementBuilder extends Builder
{
    public function search(?string $search): self
    {
        return $this->when($search, fn (self $q): self => $q->where(fn (self $q): self => $q
            ->where('reference_type', 'like', "%$search%")
            ->orWhere('note', 'like', "%$search%")
        ));
    }

    public function in(): self
    {
        return $this->where('type', StockMovementTypeEnum::In);
    }

    public function out(): self
    {
        return $this->where('type', StockMovementTypeEnum::Out);
    }

    public function transfer(): self
    {
        return $this->where('type', StockMovementTypeEnum::Transfer);
    }

    public function adjustment(): self
    {
        return $this->where('type', StockMovementTypeEnum::Adjustment);
    }

    public function type(?string $type): self
    {
        return $this->when($type, fn (self $q): self => $q->where('type', $type));
    }

    public function recent(int $days = 30): self
    {
        return $this->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * @param array{
     *     search?: string|null,
     *     type?: string|null,
     *     sort?: string|null,
     *     direction?: 'asc'|'desc'|string|null
     * } $filters
     */
    public function applyFilters(array $filters): self
    {
        return $this
            ->search($filters['search'] ?? null)
            ->type($filters['type'] ?? null)
            ->when(
                $filters['sort'] ?? null,
                fn (self $q, string $col): self => $q->orderBy($col, $filters['direction'] ?? 'asc'),
                fn (self $q): self => $q->latest()
            );
    }

    /**
     * @param array{
     *     search?: string|null,
     *     type?: string|null,
     *     sort?: string|null,
     *     direction?: 'asc'|'desc'|string|null
     * } $filters
     * @return LengthAwarePaginator<int, StockMovement>
     */
    public function paginateWithFilters(array $filters, ?int $perPage = null): LengthAwarePaginator
    {
        return $this
            ->applyFilters($filters)
            ->with(['product', 'warehouse', 'batch'])
            ->paginate($perPage ?? 25)
            ->withQueryString();
    }
}
