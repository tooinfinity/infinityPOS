<?php

declare(strict_types=1);

namespace App\Models;

use App\Builders\ProductBuilder;
use App\Enums\MediaCollection;
use App\Models\Scopes\ActiveScope;
use Carbon\CarbonInterface;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property-read int $id
 * @property-read int|null $category_id
 * @property-read int|null $brand_id
 * @property-read int $unit_id
 * @property-read string $name
 * @property-read string $sku
 * @property-read string $barcode
 * @property-read string|null $description
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
 *
 * @method static ProductBuilder query()
 */
#[ScopedBy([ActiveScope::class])]
final class Product extends Model implements HasMedia
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use InteractsWithMedia;

    /**
     * @return Builder<self>
     */
    public static function withInactive(): Builder
    {
        return self::query()->withoutGlobalScope(ActiveScope::class);
    }

    public function newEloquentBuilder(mixed $query): ProductBuilder
    {
        return new ProductBuilder($query);
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
            'cost_price' => 'integer',
            'selling_price' => 'integer',
            'alert_quantity' => 'integer',
            'track_inventory' => 'boolean',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollection::ProductThumbnail->value)
            ->acceptsMimeTypes(MediaCollection::ProductThumbnail->allowedMimeTypes())
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->performOnCollections(MediaCollection::ProductThumbnail->value)
            ->width(400)
            ->height(400)
            ->sharpen(5);
    }

    protected function getThumbnailUrlAttribute(): string
    {
        return $this->getFirstMediaUrl(MediaCollection::ProductThumbnail->value, 'thumb');
    }

    /**
     * @return array{id: int, url: string, thumb: string, size: string}|null
     */
    protected function getThumbnailAttribute(): ?array
    {
        $media = $this->getFirstMedia(MediaCollection::ProductThumbnail->value);

        if (! $media instanceof Media) {
            return null;
        }

        return [
            'id' => $media->id,
            'url' => $media->getUrl(),
            'thumb' => $media->getUrl('thumb'),
            'size' => $media->human_readable_size,
        ];
    }
}
