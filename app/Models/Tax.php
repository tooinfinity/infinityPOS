<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TaxTypeEnum;
use Carbon\CarbonInterface;
use Database\Factories\TaxFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $tax_type
 * @property-read float $rate
 * @property-read bool $is_active
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Collection<int, Product> $products
 */
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
        return $this->tax_type === TaxTypeEnum::PERCENTAGE->value;
    }

    /**
     * Check if tax is fixed type.
     */
    public function isFixed(): bool
    {
        return $this->tax_type === TaxTypeEnum::FIXED->value;
    }

    /**
     * Calculate tax amount for a given value.
     */
    public function calculate(float $value): float
    {
        return match ($this->tax_type) {
            TaxTypeEnum::PERCENTAGE->value => ($value * $this->rate) / 100,
            TaxTypeEnum::FIXED->value => $this->rate,
            default => 0,
        };
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'tax_type' => 'string',
            'rate' => 'decimal:2',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
