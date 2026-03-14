<?php

declare(strict_types=1);

namespace App\Models;

use App\Builders\BrandBuilder;
use App\Enums\MediaCollection;
use App\Models\Scopes\ActiveScope;
use Carbon\CarbonInterface;
use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read bool $is_active
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Collection<int, Product> $products
 */
#[ScopedBy([ActiveScope::class])]
final class Brand extends Model implements HasMedia
{
    /** @use HasFactory<BrandFactory> */
    use HasFactory;

    use InteractsWithMedia;

    /**
     * @return Builder<self>
     */
    public static function withInactive(): Builder
    {
        return self::query()->withoutGlobalScope(ActiveScope::class);
    }

    public function newEloquentBuilder(mixed $query): BrandBuilder
    {
        return new BrandBuilder($query);
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollection::BrandLogo->value)
            ->acceptsMimeTypes(MediaCollection::BrandLogo->allowedMimeTypes())
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->performOnCollections(MediaCollection::BrandLogo->value)
            ->width(200)
            ->height(200)
            ->sharpen(10);
    }

    protected function getLogoUrlAttribute(): string
    {
        return $this->getFirstMediaUrl(MediaCollection::BrandLogo->value, 'thumb')
            ?: $this->getFirstMediaUrl(MediaCollection::BrandLogo->value);
    }

    /**
     * @return array{id: int, url: string, thumb: string, size: string}|null
     */
    protected function getLogoAttribute(): ?array
    {
        $media = $this->getFirstMedia(MediaCollection::BrandLogo->value);

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
