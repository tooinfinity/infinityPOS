<?php

declare(strict_types=1);

namespace App\Builders;

use App\Enums\PaymentStateEnum;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends Builder<Payment>
 */
final class PaymentBuilder extends Builder
{
    public function search(?string $search): self
    {
        return $this->when($search, fn (self $q): self => $q->where(fn (self $q): self => $q
            ->where('reference_no', 'like', "%$search%")
            ->orWhere('note', 'like', "%$search%")
        ));
    }

    public function status(?string $status): self
    {
        return $this->when($status, fn (self $q): self => $q->where('status', $status));
    }

    public function active(): self
    {
        return $this->where('status', PaymentStateEnum::Active);
    }

    public function voided(): self
    {
        return $this->where('status', PaymentStateEnum::Voided);
    }

    public function recent(int $days = 30): self
    {
        return $this->where('payment_date', '>=', now()->subDays($days));
    }

    public function today(): self
    {
        return $this->whereDate('payment_date', today());
    }

    public function refunds(): self
    {
        return $this->where('amount', '<', 0);
    }

    public function activeForPayable(string $payableType, int $payableId): self
    {
        return $this->where('payable_type', $payableType)
            ->where('payable_id', $payableId)
            ->where('status', PaymentStateEnum::Active);
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
     * @return LengthAwarePaginator<int, Payment>
     */
    public function paginateWithFilters(array $filters, ?int $perPage = null): LengthAwarePaginator
    {
        return $this
            ->applyFilters($filters)
            ->with(['paymentMethod', 'user'])
            ->paginate($perPage ?? 25)
            ->withQueryString();
    }
}
