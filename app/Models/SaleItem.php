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
 * @property-read int $id
 * @property-read int $sale_id
 * @property-read int $product_id
 * @property-read float $quantity
 * @property-read float $price
 * @property-read float $cost
 * @property-read float|null $discount
 * @property-read float|null $tax_amount
 * @property-read float $total
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
            'id' => 'integer',
            'sale_id' => 'integer',
            'product_id' => 'integer',
            'quantity' => 'decimal:2',
            'price' => 'decimal:2',
            'cost' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'batch_number' => 'string',
            'expiry_date' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
