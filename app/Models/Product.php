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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read string|null $sku
 * @property-read string|null $barcode
 * @property-read string $name
 * @property-read string|null $description
 * @property-read string|null $image
 * @property-read int|null $category_id
 * @property-read int|null $brand_id
 * @property-read int|null $unit_id
 * @property-read int|null $tax_id
 * @property-read float $cost
 * @property-read float $price
 * @property-read float $alert_quantity
 * @property-read bool $has_batches
 * @property-read bool $is_active
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Category|null $category
 * @property-read Brand|null $brand
 * @property-read Unit|null $unit
 * @property-read Tax|null $tax
 * @property-read Collection<int, SaleItem> $saleItems
 * @property-read Collection<int, PurchaseItem> $purchaseItems
 * @property-read Collection<int, Store> $stores
 */
#[ScopedBy(ActiveScope::class)]
final class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

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
     * @return BelongsTo<Tax, $this>
     */
    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    /**
     * @return HasMany<SaleItem, $this>
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * @return HasMany<PurchaseItem, $this>
     */
    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * @return BelongsToMany<Store, $this>
     */
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_stock')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    #[Scope]
    protected function lowStock(Builder $query): void
    {
        $query->whereRaw('(SELECT SUM(quantity) FROM store_stock WHERE product_id = products.id) <= alert_quantity');
    }

    #[Scope]
    protected function withBatches(Builder $query): void
    {
        $query->where('has_batches', true);
    }

    protected function totalStock(): Attribute
    {
        return Attribute::make(
            get: fn (): float => (float) $this->stores()->sum('quantity')
        );
    }

    protected function isLowStock(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->total_stock <= $this->alert_quantity
        );
    }

    protected function profitMargin(): Attribute
    {
        return Attribute::make(
            get: fn (): float => $this->cost <= 0 ? 0.0 : (($this->price - $this->cost) / $this->cost) * 100
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'sku' => 'string',
            'barcode' => 'string',
            'name' => 'string',
            'description' => 'string',
            'image' => 'string',
            'category_id' => 'integer',
            'brand_id' => 'integer',
            'unit_id' => 'integer',
            'tax_id' => 'integer',
            'cost' => 'decimal:2',
            'price' => 'decimal:2',
            'alert_quantity' => 'decimal:2',
            'has_batches' => 'boolean',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
