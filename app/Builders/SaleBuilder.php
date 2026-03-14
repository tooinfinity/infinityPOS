<?php

declare(strict_types=1);

namespace App\Builders;

use App\Enums\PaymentStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * @extends Builder<Sale>
 */
final class SaleBuilder extends Builder
{
    public function search(?string $search): self
    {
        return $this->when($search, fn (self $q): self => $q->where(fn (self $q): self => $q
            ->where('reference_no', 'like', "%$search%")
            ->orWhereHas('customer', fn (Builder $q): Builder => $q->where('name', 'like', "%$search%"))
        ));
    }

    public function status(?string $status): self
    {
        return $this->when($status, fn (self $q): self => $q->where('status', $status));
    }

    public function pending(): self
    {
        return $this->where('status', SaleStatusEnum::Pending);
    }

    public function completed(): self
    {
        return $this->where('status', SaleStatusEnum::Completed);
    }

    public function cancelled(): self
    {
        return $this->where('status', SaleStatusEnum::Cancelled);
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

    public function today(): self
    {
        return $this->whereDate('sale_date', today());
    }

    public function withDueAmount(): self
    {
        return $this->select('*')->addSelect([
            'due_amount' => DB::raw('CASE WHEN total_amount > paid_amount THEN total_amount - paid_amount ELSE 0 END'),
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
            ->paymentStatus($filters['payment_status'] ?? null)
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
     * @return LengthAwarePaginator<int, Sale>
     */
    public function paginateWithFilters(array $filters, ?int $perPage = null): LengthAwarePaginator
    {
        return $this
            ->applyFilters($filters)
            ->with(['customer', 'warehouse'])
            ->paginate($perPage ?? 25)
            ->withQueryString();
    }
}
