<?php

declare(strict_types=1);

namespace App\Models;

use App\Collections\ProductCollection;
use App\Enums\ProductUnitEnum;
use Carbon\CarbonInterface;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read int|null $category_id
 * @property-read string $name
 * @property-read string $sku
 * @property-read string|null $barcode
 * @property-read string|null $description
 * @property-read ProductUnitEnum $unit
 * @property-read int $selling_price
 * @property-read int $alert_quantity
 * @property-read string|null $image
 * @property-read bool $is_active
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * @param  array<int, static>  $models
     * @return ProductCollection<int, static>
     */
    public function newCollection(array $models = []): ProductCollection
    {
        return new ProductCollection($models);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<Inventory, $this>
     */
    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * @return HasMany<InventoryBatch, $this>
     */
    public function inventoryBatches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class);
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
     * @return HasMany<InvoiceItem, $this>
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * @return HasMany<ReturnItem, $this>
     */
    public function returnItems(): HasMany
    {
        return $this->hasMany(ReturnItem::class);
    }

    /**
     * @return HasMany<StockAdjustment, $this>
     */
    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function isLowStock(int $storeId): bool
    {
        $inventory = $this->inventory()->where('store_id', $storeId)->first();

        return $inventory && $inventory->total_quantity <= $this->alert_quantity;
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
            'category_id' => 'integer',
            'name' => 'string',
            'sku' => 'string',
            'barcode' => 'string',
            'description' => 'string',
            'unit' => ProductUnitEnum::class,
            'selling_price' => 'integer',
            'alert_quantity' => 'integer',
            'image' => 'string',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
