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
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
 * @property-read int $change_amount
 * @property-read PaymentStatusEnum $payment_status
 * @property-read string|null $note
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
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
     * @return Builder<Sale>
     */
    #[Scope]
    protected function pending(Builder $query): Builder
    {
        return $query->where('status', SaleStatusEnum::Pending->value);
    }

    /**
     * @param  Builder<Sale>  $query
     * @return Builder<Sale>
     */
    #[Scope]
    protected function completed(Builder $query): Builder
    {
        return $query->where('status', SaleStatusEnum::Completed->value);
    }

    /**
     * @param  Builder<Sale>  $query
     * @return Builder<Sale>
     */
    #[Scope]
    protected function cancelled(Builder $query): Builder
    {
        return $query->where('status', SaleStatusEnum::Cancelled->value);
    }

    /**
     * @param  Builder<Sale>  $query
     * @return Builder<Sale>
     */
    #[Scope]
    protected function unpaid(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatusEnum::Unpaid->value);
    }

    /**
     * @param  Builder<Sale>  $query
     * @return Builder<Sale>
     */
    #[Scope]
    protected function partiallyPaid(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatusEnum::Partial->value);
    }

    /**
     * @param  Builder<Sale>  $query
     * @return Builder<Sale>
     */
    #[Scope]
    protected function paid(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatusEnum::Paid->value);
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
     * @return Attribute<int, null>
     */
    protected function profit(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->items->sum(fn (SaleItem $item): int => ($item->unit_price - $item->unit_cost) * $item->quantity),
        );
    }
}
