<?php

declare(strict_types=1);

namespace App\Models;

use App\Collections\InventoryBatchCollection;
use Carbon\CarbonInterface;
use Database\Factories\InventoryBatchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read int $store_id
 * @property-read int $product_id
 * @property-read int $purchase_item_id
 * @property-read int $quantity_received
 * @property-read int $quantity_remaining
 * @property-read int $unit_cost
 * @property-read CarbonInterface $batch_date
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class InventoryBatch extends Model
{
    /** @use HasFactory<InventoryBatchFactory> */
    use HasFactory;

    /**
     * @param  array<int, static>  $models
     * @return InventoryBatchCollection<int, static>
     */
    public function newCollection(array $models = []): InventoryBatchCollection
    {
        return new InventoryBatchCollection($models);
    }

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<PurchaseItem, $this>
     */
    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    /**
     * @return HasMany<SaleItemBatch, $this>
     */
    public function saleItemBatches(): HasMany
    {
        return $this->hasMany(SaleItemBatch::class);
    }

    /**
     * Check if batch has remaining quantity.
     */
    public function hasRemainingQuantity(): bool
    {
        return $this->quantity_remaining > 0;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'store_id' => 'integer',
            'product_id' => 'integer',
            'purchase_item_id' => 'integer',
            'quantity_received' => 'integer',
            'quantity_remaining' => 'integer',
            'unit_cost' => 'integer',
            'batch_date' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
