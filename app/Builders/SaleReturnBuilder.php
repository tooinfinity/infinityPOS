<?php

declare(strict_types=1);

namespace App\Builders;

use App\Enums\PaymentStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Models\SaleReturn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends Builder<SaleReturn>
 */
final class SaleReturnBuilder extends Builder
{
    public function search(?string $search): self
    {
        return $this->when($search, fn (self $q): self => $q->where(fn (self $q): self => $q
            ->where('reference_no', 'like', "%$search%")
        ));
    }

    public function pending(): self
    {
        return $this->where('status', ReturnStatusEnum::Pending);
    }

    public function completed(): self
    {
        return $this->where('status', ReturnStatusEnum::Completed);
    }

    public function status(?string $status): self
    {
        return $this->when($status, fn (self $q): self => $q->where('status', $status));
    }

    public function unpaid(): self
    {
        return $this->where('payment_status', PaymentStatusEnum::Unpaid);
    }

    public function partiallyPaid(): self
    {
        return $this->where('payment_status', PaymentStatusEnum::Partial);
    }

    public function paid(): self
    {
        return $this->where('payment_status', PaymentStatusEnum::Paid);
    }

    public function paymentStatus(?string $status): self
    {
        return $this->when($status, fn (self $q): self => $q->where('payment_status', $status));
    }

    /**
     * @param array{
     *     search?: string|null,
     *     status?: string|null,
     *     payment_status?: string|null,
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
     *     payment_status?: string|null,
     *     sort?: string|null,
     *     direction?: 'asc'|'desc'|string|null
     * } $filters
     * @return LengthAwarePaginator<int, SaleReturn>
     */
    public function paginateWithFilters(array $filters, ?int $perPage = null): LengthAwarePaginator
    {
        return $this
            ->applyFilters($filters)
            ->with(['sale', 'customer'])
            ->paginate($perPage ?? 25)
            ->withQueryString();
    }
}
