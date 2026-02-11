<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentStatusEnum;
use App\Enums\PurchaseStatusEnum;
use Carbon\CarbonInterface;
use Database\Factories\PurchaseFactory;
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
 * @property-read int $supplier_id
 * @property-read int $warehouse_id
 * @property-read int|null $user_id
 * @property-read string $reference_no
 * @property-read PurchaseStatusEnum $status
 * @property-read CarbonInterface $purchase_date
 * @property-read int $total_amount
 * @property-read int $paid_amount
 * @property-read PaymentStatusEnum $payment_status
 * @property-read string|null $note
 * @property-read string|null $document
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Purchase extends Model
{
    /** @use HasFactory<PurchaseFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
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
     * @return HasMany<PurchaseItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
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
     * @return HasMany<PurchaseReturn, $this>
     */
    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'supplier_id' => 'integer',
            'warehouse_id' => 'integer',
            'user_id' => 'integer',
            'reference_no' => 'string',
            'status' => PurchaseStatusEnum::class,
            'purchase_date' => 'datetime',
            'total_amount' => 'integer',
            'paid_amount' => 'integer',
            'payment_status' => PaymentStatusEnum::class,
            'note' => 'string',
            'document' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<Purchase>  $query
     * @return Builder<Purchase>
     */
    #[Scope]
    protected function pending(Builder $query): Builder
    {
        return $query->where('status', PurchaseStatusEnum::Pending->value);
    }

    /**
     * @param  Builder<Purchase>  $query
     * @return Builder<Purchase>
     */
    #[Scope]
    protected function ordered(Builder $query): Builder
    {
        return $query->where('status', PurchaseStatusEnum::Ordered->value);
    }

    /**
     * @param  Builder<Purchase>  $query
     * @return Builder<Purchase>
     */
    #[Scope]
    protected function received(Builder $query): Builder
    {
        return $query->where('status', PurchaseStatusEnum::Received->value);
    }

    /**
     * @param  Builder<Purchase>  $query
     * @return Builder<Purchase>
     */
    #[Scope]
    protected function cancelled(Builder $query): Builder
    {
        return $query->where('status', PurchaseStatusEnum::Cancelled->value);
    }

    /**
     * @param  Builder<Purchase>  $query
     * @return Builder<Purchase>
     */
    #[Scope]
    protected function unpaid(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatusEnum::Unpaid->value);
    }

    /**
     * @param  Builder<Purchase>  $query
     * @return Builder<Purchase>
     */
    #[Scope]
    protected function partiallyPaid(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatusEnum::Partial->value);
    }

    /**
     * @param  Builder<Purchase>  $query
     * @return Builder<Purchase>
     */
    #[Scope]
    protected function paid(Builder $query): Builder
    {
        return $query->where('payment_status', 'paid');
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
}
