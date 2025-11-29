<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PurchaseStatusEnum;
use Carbon\CarbonInterface;
use Database\Factories\PurchaseFactory;
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
 * @property-read int|null $supplier_id
 * @property-read int $store_id
 * @property-read float $subtotal
 * @property-read float|null $discount
 * @property-read float|null $tax
 * @property-read float $total
 * @property-read float $paid
 * @property-read PurchaseStatusEnum $status
 * @property-read string|null $notes
 * @property-read int|null $user_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Supplier|null $supplier
 * @property-read Store $store
 * @property-read User|null $user
 * @property-read Collection<int, PurchaseItem> $items
 * @property-read Collection<int, PurchaseReturn> $returns
 * @property-read Collection<int, Payment> $payments
 * @property-read Collection<int, StockMovement> $stockMovements
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
     * @return HasMany<PurchaseItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * @return HasMany<PurchaseReturn, $this>
     */
    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
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
     * Check if the purchase is pending.
     */
    public function isPending(): bool
    {
        return $this->status === PurchaseStatusEnum::PENDING;
    }

    /**
     * Check if the purchase is received.
     */
    public function isReceived(): bool
    {
        return $this->status === PurchaseStatusEnum::RECEIVED;
    }

    /**
     * Check if the purchase is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === PurchaseStatusEnum::CANCELLED;
    }

    /**
     * Check if the purchase is fully paid.
     */
    public function isFullyPaid(): bool
    {
        return $this->remaining_amount <= 0;
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'reference' => 'string',
            'supplier_id' => 'integer',
            'store_id' => 'integer',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'paid' => 'decimal:2',
            'status' => PurchaseStatusEnum::class,
            'notes' => 'string',
            'user_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return Attribute<float, never>
     */
    protected function remainingAmount(): Attribute
    {
        return Attribute::make(
            get: fn (): float => max(0, (float) $this->total - (float) $this->paid)
        );
    }

    /**
     * @return Attribute<float, never>
     */
    protected function balance(): Attribute
    {
        return Attribute::make(
            get: fn (): float => $this->total - $this->paid
        );
    }
}
