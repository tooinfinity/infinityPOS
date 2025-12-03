<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SaleStatusEnum;
use Carbon\CarbonInterface;
use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read int $id
 * @property-read string $reference
 * @property-read int|null $client_id
 * @property-read int $store_id
 * @property-read float $subtotal
 * @property-read float|null $discount
 * @property-read float|null $tax
 * @property-read float $total
 * @property-read float $paid
 * @property-read string $status
 * @property-read string|null $notes
 * @property-read int $created_by
 * @property-read int|null $updated_by
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Client|null $client
 * @property-read Store $store
 * @property-read User $creator
 * @property-read User|null $updater
 * @property-read Collection<int, SaleItem> $items
 * @property-read Collection<int, SaleReturn> $returns
 * @property-read Invoice|null $invoice
 * @property-read Collection<int, Payment> $payments
 * @property-read Collection<int, StockMovement> $stockMovements
 */
final class Sale extends Model
{
    /** @use HasFactory<SaleFactory> */
    use HasFactory;

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
     * @return HasMany<SaleItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * @return HasMany<SaleReturn, $this>
     */
    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    /**
     * @return HasOne<Invoice, $this>
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
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
            ->where('stock_movements.type', 'sale');
    }

    /**
     * Check if the sale is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === SaleStatusEnum::COMPLETED->value;
    }

    /**
     * Check if the sale is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === SaleStatusEnum::CANCELLED->value;
    }

    /**
     * Check if the sale is fully paid.
     */
    public function isFullyPaid(): bool
    {
        return $this->getRemainingAmountAttribute() <= 0;
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'reference' => 'string',
            'client_id' => 'integer',
            'store_id' => 'integer',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'paid' => 'decimal:2',
            'status' => 'string',
            'notes' => 'string',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
