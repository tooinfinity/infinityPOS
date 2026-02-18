<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Carbon\CarbonInterface;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read int|null $category_id
 * @property-read int|null $brand_id
 * @property-read int $unit_id
 * @property-read string $name
 * @property-read string $sku
 * @property-read string $barcode
 * @property-read string|null $description
 * @property-read string|null $image
 * @property-read int $cost_price
 * @property-read int $selling_price
 * @property-read int $alert_quantity
 * @property-read bool $track_inventory
 * @property-read bool $is_active
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Category|null $category
 * @property-read Brand|null $brand
 * @property-read Unit $unit
 * @property-read Collection<int, Batch> $batches
 * @property-read Collection<int, StockMovement> $stockMovements
 * @property-read Collection<int, PurchaseItem> $purchaseItems
 * @property-read Collection<int, SaleItem> $saleItems
 * @property-read Collection<int, StockTransferItem> $stockTransferItems
 * @property-read Collection<int, SaleReturnItem> $saleReturnItems
 * @property-read Collection<int, PurchaseReturnItem> $purchaseReturnItems
 */
#[ScopedBy([ActiveScope::class])]
final class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * @return Builder<self>
     */
    public static function withInactive(): Builder
    {
        return self::query()->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * @return HasMany<Batch, $this>
     */
    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
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
            'category_id' => 'integer',
            'brand_id' => 'integer',
            'unit_id' => 'integer',
            'name' => 'string',
            'sku' => 'string',
            'barcode' => 'string',
            'description' => 'string',
            'image' => 'string',
            'cost_price' => 'integer',
            'selling_price' => 'integer',
            'alert_quantity' => 'integer',
            'track_inventory' => 'boolean',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    #[Scope]
    protected function lowStock(Builder $query): Builder
    {
        return $query->whereRaw(
            '(SELECT COALESCE(SUM(quantity), 0) FROM batches WHERE batches.product_id = products.id) <= products.alert_quantity'
        )->where('track_inventory', true);
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    #[Scope]
    protected function outOfStock(Builder $query): Builder
    {
        return $query->whereRaw(
            '(SELECT COALESCE(SUM(quantity), 0) FROM batches WHERE batches.product_id = products.id) <= 0'
        )->where('track_inventory', true);
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    #[Scope]
    protected function search(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $q) use ($search): void {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%");
        });
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    #[Scope]
    protected function tracked(Builder $query): Builder
    {
        return $query->where('track_inventory', true);
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    #[Scope]
    protected function withStockQuantity(Builder $query): Builder
    {
        return $query->addSelect([
            'stock_quantity' => Batch::query()->selectRaw('COALESCE(SUM(quantity), 0)')
                ->whereColumn('batches.product_id', 'products.id'),
        ]);
    }

    /**
     * @return Attribute<bool, null>
     */
    protected function isLowStock(): Attribute
    {
        return Attribute::make(
            get: function (): bool {
                if (! $this->track_inventory) {
                    return false;
                }

                if (array_key_exists('stock_quantity', $this->attributes)) {
                    /** @var int $stockQuantity */
                    $stockQuantity = $this->attributes['stock_quantity'];
                    $quantity = $stockQuantity;
                } else {
                    $quantity = $this->getTotalQuantity();
                }

                return $quantity <= $this->alert_quantity;
            },
        );
    }

    /**
     * @return Attribute<bool, null>
     */
    protected function isOutOfStock(): Attribute
    {
        return Attribute::make(
            get: function (): bool {
                if (! $this->track_inventory) {
                    return false;
                }

                if (array_key_exists('stock_quantity', $this->attributes)) {
                    /** @var int $stockQuantity */
                    $stockQuantity = $this->attributes['stock_quantity'];
                    $quantity = $stockQuantity;
                } else {
                    $quantity = $this->getTotalQuantity();
                }

                return $quantity <= 0;
            },
        );
    }

    /**
     * Get total quantity from all batches
     */
    protected function getTotalQuantity(): int
    {
        if (array_key_exists('stock_quantity', $this->attributes)) {
            /** @var int $stockQuantity */
            $stockQuantity = $this->attributes['stock_quantity'];

            return $stockQuantity;
        }

        /** @var int|null $sum */
        $sum = $this->batches()->sum('quantity');

        return (int) ($sum ?? 0);
    }

    /**
     * @return Attribute<int|float, null>
     */
    protected function profitMargin(): Attribute
    {
        return Attribute::make(
            get: fn (): int|float => $this->selling_price > 0
                ? (($this->selling_price - $this->cost_price) / $this->selling_price) * 100
                : 0,
        );
    }
}
