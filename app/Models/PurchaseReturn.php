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
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property-read int $id
 * @property-read string $reference
 * @property-read int $subtotal
 * @property-read int|null $discount
 * @property-read int|null $tax
 * @property-read int $total
 * @property-read int $refunded
 * @property-read string $status
 * @property-read string|null $reason
 * @property-read string|null $notes
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
     * @return MorphMany<Payment, $this>
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'related');
    }

    /**
     * @return MorphMany<StockMovement, $this>
     */
    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'source');
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
            'subtotal' => 'integer',
            'discount' => 'integer',
            'tax' => 'integer',
            'total' => 'integer',
            'refunded' => 'integer',
            'status' => PurchaseReturnStatusEnum::class,
            'reason' => 'string',
            'notes' => 'string',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
