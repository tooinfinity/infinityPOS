<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SaleReturnStatusEnum;
use Carbon\CarbonInterface;
use Database\Factories\SaleReturnFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
 * @property-read string $status
 * @property-read string|null $reason
 * @property-read string|null $notes
 * @property-read int $created_by
 * @property-read int|null $updated_by
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Sale|null $sale
 * @property-read Client|null $client
 * @property-read Store $store
 * @property-read User $creator
 * @property-read User|null $updater
 * @property-read Collection<int, SaleReturnItem> $items
 * @property-read Collection<int, Payment> $payments
 * @property-read Collection<int, StockMovement> $stockMovements
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
     * @return HasMany<SaleReturnItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'related_id')
            ->where('payments.type', 'sale');
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
        return $this->status === SaleReturnStatusEnum::PENDING->value;
    }

    /**
     * Check if return is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === SaleReturnStatusEnum::COMPLETED->value;
    }

    /**
     * Check if return is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === SaleReturnStatusEnum::CANCELLED->value;
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
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
            'status' => 'string',
            'reason' => 'string',
            'notes' => 'string',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
