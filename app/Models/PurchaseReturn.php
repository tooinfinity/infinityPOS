<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentStatusEnum;
use App\Enums\ReturnStatusEnum;
use Carbon\CarbonInterface;
use Database\Factories\PurchaseReturnFactory;
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
 * @property-read int $purchase_id
 * @property-read int $warehouse_id
 * @property-read int|null $user_id
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
 * @property-read Purchase $purchase
 * @property-read Warehouse $warehouse
 * @property-read User|null $user
 * @property-read Collection<int, PurchaseReturnItem> $items
 * @property-read Collection<int, StockMovement> $stockMovements
 */
final class PurchaseReturn extends Model
{
    /** @use HasFactory<PurchaseReturnFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Purchase, $this>
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
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
     * @return HasMany<PurchaseReturnItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    /**
     * @return MorphMany<StockMovement, $this>
     */
    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'purchase_id' => 'integer',
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
     * @param  Builder<PurchaseReturn>  $query
     * @return Builder<PurchaseReturn>
     */
    #[Scope]
    protected function pending(Builder $query): Builder
    {
        return $query->where('status', ReturnStatusEnum::Pending->value);
    }

    /**
     * @param  Builder<PurchaseReturn>  $query
     * @return Builder<PurchaseReturn>
     */
    #[Scope]
    protected function completed(Builder $query): Builder
    {
        return $query->where('status', ReturnStatusEnum::Completed->value);
    }

    /**
     * @param  Builder<PurchaseReturn>  $query
     * @return Builder<PurchaseReturn>
     */
    #[Scope]
    protected function unpaid(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatusEnum::Unpaid->value);
    }

    /**
     * @param  Builder<PurchaseReturn>  $query
     * @return Builder<PurchaseReturn>
     */
    #[Scope]
    protected function partiallyPaid(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatusEnum::Partial->value);
    }

    /**
     * @param  Builder<PurchaseReturn>  $query
     * @return Builder<PurchaseReturn>
     */
    #[Scope]
    protected function paid(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatusEnum::Paid->value);
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
     * @param  Builder<PurchaseReturn>  $query
     * @return Builder<PurchaseReturn>
     */
    #[Scope]
    protected function withDueAmount(Builder $query): Builder
    {
        return $query->select('*')->addSelect([
            'due_amount' => DB::raw('CASE WHEN total_amount > paid_amount THEN total_amount - paid_amount ELSE 0 END'),
        ]);
    }
}
