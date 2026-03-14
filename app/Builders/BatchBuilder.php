<?php

declare(strict_types=1);

namespace App\Builders;

use App\Models\Batch;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends Builder<Batch>
 */
final class BatchBuilder extends Builder
{
    public function search(?string $search): self
    {
        return $this->when($search, fn (self $q): self => $q->where(fn (self $q): self => $q
            ->where('batch_number', 'like', "%$search%")
            ->orWhereHas('product', fn (Builder $q): Builder => $q->where('name', 'like', "%$search%"))
        ));
    }

    public function inStock(): self
    {
        return $this->where('quantity', '>', 0);
    }

    public function expired(): self
    {
        return $this->whereNotNull('expires_at')
            ->where('expires_at', '<', now());
    }

    public function expiringSoon(int $days = 30): self
    {
        return $this->whereNotNull('expires_at')
            ->where('expires_at', '>=', now())
            ->where('expires_at', '<=', now()->addDays($days));
    }

    public function fifo(): self
    {
        return $this->oldest();
    }

    public function fefo(): self
    {
        return $this->orderByRaw('expires_at IS NULL, expires_at ASC');
    }

    public function matching(
        int $productId,
        int $warehouseId,
        int $costAmount,
        ?CarbonInterface $expiresAt = null,
    ): self {
        return $this->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('cost_amount', $costAmount)
            ->when(
                $expiresAt instanceof CarbonInterface,
                fn (Builder $q) => $q->where('expires_at', $expiresAt),
                fn (Builder $q) => $q->whereNull('expires_at'),
            );
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
     * @return LengthAwarePaginator<int, Batch>
     */
    public function paginateWithFilters(array $filters, ?int $perPage = null): LengthAwarePaginator
    {
        return $this
            ->applyFilters($filters)
            ->with(['product', 'warehouse'])
            ->paginate($perPage ?? 25)
            ->withQueryString();
    }
}
