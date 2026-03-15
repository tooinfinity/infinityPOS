<?php

declare(strict_types=1);

namespace App\Builders;

use App\Enums\PaymentStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Models\PurchaseReturn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * @extends Builder<PurchaseReturn>
 */
final class PurchaseReturnBuilder extends Builder
{
    public function search(?string $search): self
    {
        return $this->when($search, fn (self $q): self => $q->where(fn (self $q): self => $q
            ->where('reference_no', 'like', "%$search%")
        ));
    }

    public function status(?string $status): self
    {
        return $this->when($status, fn (self $q): self => $q->where('status', $status));
    }

    public function paymentStatus(?string $status): self
    {
        return $this->when($status, fn (self $q): self => $q->where('payment_status', $status));
    }

    public function pending(): self
    {
        return $this->where('status', ReturnStatusEnum::Pending);
    }

    public function completed(): self
    {
        return $this->where('status', ReturnStatusEnum::Completed);
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

    public function withDueAmount(): self
    {
        return $this->select('*')->addSelect([
            'due_amount' => DB::raw(
                'CASE WHEN total_amount > paid_amount THEN total_amount - paid_amount ELSE 0 END'
            ),
        ]);
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
     * @return LengthAwarePaginator<int, PurchaseReturn>
     */
    public function paginateWithFilters(array $filters, ?int $perPage = null): LengthAwarePaginator
    {
        return $this
            ->applyFilters($filters)
            ->with(['purchase.supplier', 'warehouse'])
            ->paginate($perPage ?? 25)
            ->withQueryString();
    }
}
