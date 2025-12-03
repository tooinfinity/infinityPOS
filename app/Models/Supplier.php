<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\SupplierFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string|null $phone
 * @property-read string|null $email
 * @property-read string|null $address
 * @property-read float $balance
 * @property-read bool $is_active
 * @property-read int|null $business_identifier_id
 * @property-read int $created_by
 * @property-read int|null $updated_by
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read BusinessIdentifier|null $businessIdentifier
 * @property-read User $creator
 * @property-read User|null $updater
 * @property-read Collection<int, Purchase> $purchases
 * @property-read Collection<int, PurchaseReturn> $purchaseReturns
 */
final class Supplier extends Model
{
    /** @use HasFactory<SupplierFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<BusinessIdentifier, $this>
     */
    public function businessIdentifier(): BelongsTo
    {
        return $this->belongsTo(BusinessIdentifier::class);
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
     * @return HasMany<Purchase, $this>
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * @return HasMany<PurchaseReturn, $this>
     */
    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'phone' => 'string',
            'email' => 'string',
            'address' => 'string',
            'balance' => 'decimal:2',
            'is_active' => 'boolean',
            'business_identifier_id' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
