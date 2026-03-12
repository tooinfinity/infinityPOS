<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentStatusEnum;
use App\Enums\SaleStatusEnum;
use Carbon\CarbonInterface;
use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

/**
 * @property-read int $id
 * @property-read int|null $customer_id
 * @property-read int $warehouse_id
 * @property-read int|null $user_id
 * @property-read string $reference_no
 * @property-read SaleStatusEnum $status
 * @property-read CarbonInterface $sale_date
 * @property-read int $total_amount
 * @property-read int $paid_amount
 * @property-read int $due_amount
 * @property-read int $change_amount
 * @property-read PaymentStatusEnum $payment_status
 * @property-read string|null $note
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Customer|null $customer
 * @property-read Warehouse $warehouse
 * @property-read User|null $user
 * @property-read Collection<int, SaleItem> $items
 * @property-read Collection<int, Payment> $payments
 * @property-read Collection<int, StockMovement> $stockMovements
 * @property-read Collection<int, SaleReturn> $returns
 */
final class Sale extends Model
{
    /** @use HasFactory<SaleFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<Warehouse, $this>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<SaleItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * @return MorphMany<Payment, $this>
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * @return MorphMany<Payment, $this>
     */
    public function activePayments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable')->active();
    }

    /**
     * @return MorphMany<StockMovement, $this>
     */
    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    /**
     * @return HasMany<SaleReturn, $this>
     */
    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'customer_id' => 'integer',
            'warehouse_id' => 'integer',
            'user_id' => 'integer',
            'reference_no' => 'string',
            'status' => SaleStatusEnum::class,
            'sale_date' => 'datetime',
            'total_amount' => 'integer',
            'paid_amount' => 'integer',
            'change_amount' => 'integer',
            'payment_status' => PaymentStatusEnum::class,
            'note' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<Sale>  $query
     */
    #[Scope]
    protected function search(Builder $query, ?string $search): void
    {
        $query->when($search, fn (Builder $query, string $search) => $query->whereAny(
            ['customer.name', 'reference_no'],
            'like',
            "%{$search}%",
        ));

    }

    /**
     * @param  Builder<Sale>  $query
     * @return Builder<Sale>
     */
    #[Scope]
    protected function pending(Builder $query): Builder
    {
        return $query->where('status', SaleStatusEnum::Pending);
    }

    /**
     * @param  Builder<Sale>  $query
     * @return Builder<Sale>
     */
    #[Scope]
    protected function completed(Builder $query): Builder
    {
        return $query->where('status', SaleStatusEnum::Completed);
    }

    /**
     * @param  Builder<Sale>  $query
     * @return Builder<Sale>
     */
    #[Scope]
    protected function cancelled(Builder $query): Builder
    {
        return $query->where('status', SaleStatusEnum::Cancelled);
    }

    /**
     * @param  Builder<Sale>  $query
     * @return Builder<Sale>
     */
    #[Scope]
    protected function unpaid(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatusEnum::Unpaid);
    }

    /**
     * @param  Builder<Sale>  $query
     * @return Builder<Sale>
     */
    #[Scope]
    protected function partiallyPaid(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatusEnum::Partial);
    }

    /**
     * @param  Builder<Sale>  $query
     * @return Builder<Sale>
     */
    #[Scope]
    protected function paid(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatusEnum::Paid);
    }

    /**
     * @param  Builder<Sale>  $query
     * @return Builder<Sale>
     */
    #[Scope]
    protected function today(Builder $query): Builder
    {
        return $query->whereDate('sale_date', today());
    }

    /**
     * @return Attribute<int, null>
     */
    protected function dueAmount(): Attribute
    {
        return Attribute::make(
            get: fn (): int => max(0, $this->total_amount - $this->paid_amount),
        );
    }

    /**
     * @param  Builder<Sale>  $query
     * @return Builder<Sale>
     */
    #[Scope]
    protected function withDueAmount(Builder $query): Builder
    {
        return $query->select('*')->addSelect([
            'due_amount' => DB::raw('CASE WHEN total_amount > paid_amount THEN total_amount - paid_amount ELSE 0 END'),
        ]);
    }

    /**
     * @return Attribute<int, null>
     */
    protected function profit(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                if (! $this->relationLoaded('items')) {
                    return 0;
                }

                return $this->items->sum(fn (SaleItem $item): int => ($item->unit_price - $item->unit_cost) * $item->quantity);
            },
        );
    }
}
