<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentStatusEnum;
use App\Enums\ReturnStatusEnum;
use Carbon\CarbonInterface;
use Database\Factories\SaleReturnFactory;
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
 * @property-read int $sale_id
 * @property-read int $warehouse_id
 * @property-read int $user_id
 * @property-read string $reference_no
 * @property-read CarbonInterface $return_date
 * @property-read int $total_amount
 * @property-read int $paid_amount
 * @property-read PaymentStatusEnum $payment_status
 * @property-read int $due_amount
 * @property-read ReturnStatusEnum $status
 * @property-read string|null $note
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Sale $sale
 * @property-read Warehouse $warehouse
 * @property-read User $user
 * @property-read Collection<int, SaleReturnItem> $items
 * @property-read Collection<int, StockMovement> $stockMovements
 * @property-read Collection<int, Payment> $payments
 */
final class SaleReturn extends Model
{
    /** @use HasFactory<SaleReturnFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Sale, $this>
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
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
     * @return HasMany<SaleReturnItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    /**
     * @return MorphMany<StockMovement, $this>
     */
    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
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
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'sale_id' => 'integer',
            'warehouse_id' => 'integer',
            'user_id' => 'integer',
            'reference_no' => 'string',
            'return_date' => 'datetime',
            'total_amount' => 'integer',
            'paid_amount' => 'integer',
            'payment_status' => PaymentStatusEnum::class,
            'status' => ReturnStatusEnum::class,
            'note' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<SaleReturn>  $query
     * @return Builder<SaleReturn>
     */
    #[Scope]
    protected function pending(Builder $query): Builder
    {
        return $query->where('status', ReturnStatusEnum::Pending);
    }

    /**
     * @param  Builder<SaleReturn>  $query
     * @return Builder<SaleReturn>
     */
    #[Scope]
    protected function completed(Builder $query): Builder
    {
        return $query->where('status', ReturnStatusEnum::Completed);
    }

    /**
     * @param  Builder<SaleReturn>  $query
     * @return Builder<SaleReturn>
     */
    #[Scope]
    protected function unpaid(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatusEnum::Unpaid);
    }

    /**
     * @param  Builder<SaleReturn>  $query
     * @return Builder<SaleReturn>
     */
    #[Scope]
    protected function partiallyPaid(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatusEnum::Partial);
    }

    /**
     * @param  Builder<SaleReturn>  $query
     * @return Builder<SaleReturn>
     */
    #[Scope]
    protected function paid(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatusEnum::Paid);
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
     * @param  Builder<SaleReturn>  $query
     * @return Builder<SaleReturn>
     */
    #[Scope]
    protected function withDueAmount(Builder $query): Builder
    {
        return $query->select('*')->addSelect([
            'due_amount' => DB::raw('CASE WHEN total_amount > paid_amount THEN total_amount - paid_amount ELSE 0 END'),
        ]);
    }
}
