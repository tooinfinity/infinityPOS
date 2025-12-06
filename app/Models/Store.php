<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\StoreFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read string $id
 * @property-read string $name
 * @property-read string|null $city
 * @property-read string|null $address
 * @property-read string|null $phone
 * @property-read bool $is_active
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read User $creator
 * @property-read User|null $updater
 * @property-read Collection<int, Product> $products
 * @property-read Collection<int, Sale> $sales
 * @property-read Collection<int, Purchase> $purchases
 * @property-read Collection<int, SaleReturn> $saleReturns
 * @property-read Collection<int, PurchaseReturn> $purchaseReturns
 * @property-read Collection<int, Moneybox> $moneyboxes
 * @property-read Collection<int, Expense> $expenses
 * @property-read Collection<int, StockMovement> $stockMovements
 * @property-read Collection<int, StockTransfer> $outgoingTransfers
 * @property-read Collection<int, StockTransfer> $incomingTransfers
 */
final class Store extends Model
{
    /** @use HasFactory<StoreFactory> */
    use HasFactory;

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
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'store_stock')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Sale, $this>
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * @return HasMany<Purchase, $this>
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * @return HasMany<SaleReturn, $this>
     */
    public function saleReturns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    /**
     * @return HasMany<PurchaseReturn, $this>
     */
    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    /**
     * @return HasMany<Moneybox, $this>
     */
    public function moneyboxes(): HasMany
    {
        return $this->hasMany(Moneybox::class);
    }

    /**
     * @return HasMany<Expense, $this>
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * @return HasMany<StockTransfer, $this>
     */
    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'from_store_id');
    }

    /**
     * @return HasMany<StockTransfer, $this>
     */
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'to_store_id');
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'city' => 'string',
            'address' => 'string',
            'phone' => 'string',
            'is_active' => 'boolean',
            'created_by' => 'string',
            'updated_by' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
