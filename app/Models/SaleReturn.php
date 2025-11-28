<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SaleReturnStatusEnum;
use Carbon\CarbonInterface;
use Database\Factories\SaleReturnFactory;
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
 * @property-read int|null $sale_id
 * @property-read int|null $client_id
 * @property-read int $store_id
 * @property-read float $subtotal
 * @property-read float $discount
 * @property-read float $tax
 * @property-read float $total
 * @property-read float $refunded
 * @property-read SaleReturnStatusEnum $status
 * @property-read string|null $reason
 * @property-read string|null $notes
 * @property-read int|null $user_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Sale|null $sale
 * @property-read Client|null $client
 * @property-read Store $store
 * @property-read User|null $user
 * @property-read Collection<int, SaleReturnItem> $items
 * @property-read Collection<int, Payment> $payments
 * @property-read Collection<int, StockMovement> $stockMovements
 * @property-read float $remaining_refund
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
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
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
     * @return HasMany<SaleReturnItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class);
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
        return $this->status === SaleReturnStatusEnum::PENDING;
    }

    /**
     * Check if return is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === SaleReturnStatusEnum::COMPLETED;
    }

    /**
     * Check if return is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === SaleReturnStatusEnum::CANCELLED;
    }

    /**
     * Check if the return is fully refunded.
     */
    public function isFullyRefunded(): bool
    {
        return $this->remaining_refund <= 0;
    }

    /**
     * Get the remaining amount to be refunded.
     */
    /**
     * @return Attribute<float, never>
     */
    protected function remainingRefund(): Attribute
    {
        return Attribute::make(
            get: fn (): float => max(0, (float) $this->total - (float) $this->refunded)
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
            'sale_id' => 'integer',
            'client_id' => 'integer',
            'store_id' => 'integer',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'refunded' => 'decimal:2',
            'status' => SaleReturnStatusEnum::class,
            'reason' => 'string',
            'notes' => 'string',
            'user_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
