<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\SaleItemFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read string $id
 * @property-read string $quantity
 * @property-read string $price
 * @property-read string $cost
 * @property-read string|null $discount
 * @property-read string|null $tax_amount
 * @property-read string $totalstring
 * @property-read string|null $batch_number
 * @property-read CarbonInterface|null $expiry_date
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Sale $sale
 * @property-read Product $product
 * @property-read Collection<int, SaleReturnItem> $returnItems
 */
final class SaleItem extends Model
{
    /** @use HasFactory<SaleItemFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Sale, $this>
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return HasMany<SaleReturnItem, $this>
     */
    public function returnItems(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'sale_id' => 'string',
            'product_id' => 'string',
            'quantity' => 'string',
            'price' => 'string',
            'cost' => 'string',
            'discount' => 'string',
            'tax_amount' => 'string',
            'total' => 'string',
            'batch_number' => 'string',
            'expiry_date' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
