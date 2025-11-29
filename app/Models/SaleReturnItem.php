<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\SaleReturnItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $sale_return_id
 * @property-read int $product_id
 * @property-read int|null $sale_item_id
 * @property-read float $quantity
 * @property-read float $price
 * @property-read float $cost
 * @property-read float $discount
 * @property-read float $tax_amount
 * @property-read float $total
 * @property-read string|null $batch_number
 * @property-read CarbonInterface|null $expiry_date
 * @property-read float|null $remaining_quantity
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read SaleReturn $saleReturn
 * @property-read Product $product
 * @property-read SaleItem|null $saleItem
 */
final class SaleReturnItem extends Model
{
    /** @use HasFactory<SaleReturnItemFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<SaleReturn, $this>
     */
    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<SaleItem, $this>
     */
    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'sale_return_id' => 'integer',
            'product_id' => 'integer',
            'sale_item_id' => 'integer',
            'quantity' => 'decimal:2',
            'price' => 'decimal:2',
            'cost' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'batch_number' => 'string',
            'expiry_date' => 'datetime',
            'remaining_quantity' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
