<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PurchaseReturnStatusEnum;
use Carbon\CarbonInterface;
use Database\Factories\PurchaseReturnFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property-read int $id
 * @property-read string $reference
 * @property-read int|null $purchase_id
 * @property-read int|null $supplier_id
 * @property-read int $store_id
 * @property-read float $total
 * @property-read float $refunded
 * @property-read PurchaseReturnStatusEnum $status
 * @property-read string|null $reason
 * @property-read string|null $notes
 * @property-read int|null $user_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Purchase|null $purchase
 * @property-read Supplier|null $supplier
 * @property-read Store $store
 * @property-read User|null $user
 * @property-read Collection<int, PurchaseReturnItem> $items
 * @property-read Collection<int, Payment> $payments
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
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
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
        return $this->morphMany(StockMovement::class, 'source');
    }

    /**
     * Check if return is pending.
     */
    public function isPending(): bool
    {
        return $this->status === PurchaseReturnStatusEnum::PENDING;
    }

    /**
     * Check if return is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === PurchaseReturnStatusEnum::COMPLETED;
    }

    /**
     * Check if return is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === PurchaseReturnStatusEnum::CANCELLED;
    }

    /**
     * Check if the return is fully refunded.
     */
    public function isFullyRefunded(): bool
    {
        return $this->getRemainingRefundAttribute() <= 0;
    }

    /**
     * Get the remaining amount to be refunded.
     */
    protected function remainingRefund(): Attribute
    {
        return Attribute::make(
            get: fn (): float => max(0, $this->total - $this->refunded)
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'reference' => 'string',
            'purchase_id' => 'integer',
            'supplier_id' => 'integer',
            'store_id' => 'integer',
            'total' => 'decimal:2',
            'refunded' => 'decimal:2',
            'status' => PurchaseReturnStatusEnum::class,
            'reason' => 'string',
            'notes' => 'string',
            'user_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
