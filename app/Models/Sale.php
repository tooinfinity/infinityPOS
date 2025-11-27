<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property-read int $id
 * @property-read string $reference
 * @property-read CarbonImmutable $date
 * @property-read int|null $client_id
 * @property-read int $store_id
 * @property-read float $subtotal
 * @property-read float|null $discount
 * @property-read float|null $tax
 * @property-read float $total
 * @property-read float $paid
 * @property-read string $status
 * @property-read string|null $notes
 * @property-read int|null $user_id
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read Client|null $client
 * @property-read Store $store
 * @property-read User|null $user
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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'reference' => 'string',
            'date' => 'date',
            'client_id' => 'integer',
            'store_id' => 'integer',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'paid' => 'decimal:2',
            'status' => 'string',
            'notes' => 'string',
            'user_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
