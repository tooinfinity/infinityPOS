<?php

declare(strict_types=1);

namespace App\Builders;

use App\Enums\StockTransferStatusEnum;
use App\Models\StockTransfer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends Builder<StockTransfer>
 */
final class StockTransferBuilder extends Builder
{
    public function search(?string $search): self
    {
        return $this->when($search, fn (self $q): self => $q->where(fn (self $q): self => $q
            ->where('reference_no', 'like', "%$search%")
        ));
    }

    public function pending(): self
    {
        return $this->where('status', StockTransferStatusEnum::Pending);
    }

    public function completed(): self
    {
        return $this->where('status', StockTransferStatusEnum::Completed);
    }

    public function cancelled(): self
    {
        return $this->where('status', StockTransferStatusEnum::Cancelled);
    }

    public function status(?string $status): self
    {
        return $this->when($status, fn (self $q): self => $q->where('status', $status));
    }

    /**
     * @param array{
     *     search?: string|null,
     *     status?: string|null,
     *     sort?: string|null,
     *     direction?: 'asc'|'desc'|string|null
     * } $filters
     */
    public function applyFilters(array $filters): self
    {
        return $this
            ->search($filters['search'] ?? null)
            ->status($filters['status'] ?? null)
            ->when(
                $filters['sort'] ?? null,
                fn (self $q, string $col): self => $q->orderBy($col, $filters['direction'] ?? 'asc'),
                fn (self $q): self => $q->latest()
            );
    }

    /**
     * @param array{
     *     search?: string|null,
     *     status?: string|null,
     *     sort?: string|null,
     *     direction?: 'asc'|'desc'|string|null
     * } $filters
     * @return LengthAwarePaginator<int, StockTransfer>
     */
    public function paginateWithFilters(array $filters, ?int $perPage = null): LengthAwarePaginator
    {
        return $this
            ->applyFilters($filters)
            ->with(['fromWarehouse', 'toWarehouse'])
            ->paginate($perPage ?? 25)
            ->withQueryString();
    }
}
