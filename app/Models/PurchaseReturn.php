<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PurchaseReturnStatusEnum;
use Carbon\CarbonInterface;
use Database\Factories\PurchaseReturnFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read string $reference
 * @property-read int|null $purchase_id
 * @property-read int|null $supplier_id
 * @property-read int $store_id
 * @property-read float $total
 * @property-read float $refunded
 * @property-read string $status
 * @property-read string|null $reason
 * @property-read string|null $notes
 * @property-read int $created_by
 * @property-read int|null $updated_by
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Purchase|null $purchase
 * @property-read Supplier|null $supplier
 * @property-read Store $store
 * @property-read User $creator
 * @property-read User|null $updater
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
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @return HasMany<PurchaseReturnItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'related_id')
            ->where('payments.type', 'purchase');
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'reference', 'reference')
            ->where('stock_movements.type', 'return');
    }

    /**
     * Check if return is pending.
     */
    public function isPending(): bool
    {
        return $this->status === PurchaseReturnStatusEnum::PENDING->value;
    }

    /**
     * Check if return is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === PurchaseReturnStatusEnum::COMPLETED->value;
    }

    /**
     * Check if return is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === PurchaseReturnStatusEnum::CANCELLED->value;
    }

    /**
     * Check if the return is fully refunded.
     */
    public function isFullyRefunded(): bool
    {
        return $this->getRemainingRefundAttribute() <= 0;
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'reference' => 'string',
            'purchase_id' => 'integer',
            'supplier_id' => 'integer',
            'store_id' => 'integer',
            'total' => 'decimal:2',
            'refunded' => 'decimal:2',
            'status' => 'string',
            'reason' => 'string',
            'notes' => 'string',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the remaining amount to be refunded.
     */
    protected function getRemainingRefundAttribute(): float
    {
        return max(0, $this->total - $this->refunded);
    }
}
