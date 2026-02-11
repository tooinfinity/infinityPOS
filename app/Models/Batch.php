<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\BatchFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read int $product_id
 * @property-read int $warehouse_id
 * @property-read string|null $batch_number
 * @property-read int $cost_amount
 * @property-read int $quantity
 * @property-read CarbonInterface|null $expires_at
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Batch extends Model
{
    /** @use HasFactory<BatchFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Warehouse, $this>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * @return HasMany<PurchaseItem, $this>
     */
    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * @return HasMany<SaleItem, $this>
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * @return HasMany<StockTransferItem, $this>
     */
    public function stockTransferItems(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    /**
     * @return HasMany<SaleReturnItem, $this>
     */
    public function saleReturnItems(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    /**
     * @return HasMany<PurchaseReturnItem, $this>
     */
    public function purchaseReturnItems(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'product_id' => 'integer',
            'warehouse_id' => 'integer',
            'batch_number' => 'string',
            'cost_amount' => 'integer',
            'quantity' => 'integer',
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<Batch>  $query
     * @return Builder<Batch>
     */
    #[Scope]
    protected function inStock(Builder $query): Builder
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * @param  Builder<Batch>  $query
     * @return Builder<Batch>
     */
    #[Scope]
    protected function expired(Builder $query): Builder
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<', now());
    }

    /**
     * @param  Builder<Batch>  $query
     * @return Builder<Batch>
     */
    #[Scope]
    protected function expiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '>=', now())
            ->where('expires_at', '<=', now()->addDays($days));
    }

    /**
     * @param  Builder<Batch>  $query
     * @return Builder<Batch>
     */
    #[Scope]
    protected function fifo(Builder $query): Builder
    {
        return $query->oldest();
    }

    /**
     * @param  Builder<Batch>  $query
     * @return Builder<Batch>
     */
    #[Scope]
    protected function fefo(Builder $query): Builder
    {
        return $query->orderByRaw('expires_at IS NULL, expires_at ASC');
    }

    /**
     * @return Attribute<bool, null>
     */
    protected function isExpired(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->expires_at && $this->expires_at->isPast(),
        );
    }

    /**
     * @return Attribute<bool, null>
     */
    protected function isExpiringSoon(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->expires_at
                && $this->expires_at->isFuture()
                && $this->expires_at->lte(now()->addDays(30)),
        );
    }
}
