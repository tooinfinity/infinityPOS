<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TaxTypeEnum;
use App\Models\Scopes\ActiveScope;
use Carbon\CarbonInterface;
use Database\Factories\TaxFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read TaxTypeEnum $type
 * @property-read string $rate
 * @property-read bool $is_active
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Collection<int, Product> $products
 */
#[ScopedBy(ActiveScope::class)]
final class Tax extends Model
{
    /** @use HasFactory<TaxFactory> */
    use HasFactory;

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Check if tax is percentage type.
     */
    public function isPercentage(): bool
    {
        return $this->type === TaxTypeEnum::PERCENTAGE;
    }

    /**
     * Check if tax is fixed type.
     */
    public function isFixed(): bool
    {
        return $this->type === TaxTypeEnum::FIXED;
    }

    /**
     * Calculate tax amount for a given value.
     */
    public function calculate(float $value): float
    {
        $rate = (float) $this->rate;

        return match ($this->type) {
            TaxTypeEnum::PERCENTAGE => ($value * $rate) / 100,
            TaxTypeEnum::FIXED => $rate,
        };
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'type' => TaxTypeEnum::class,
            'rate' => 'decimal:2',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
