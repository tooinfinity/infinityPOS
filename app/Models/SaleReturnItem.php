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
 * @property-read int $quantity
 * @property-read int $price
 * @property-read int $cost
 * @property-read int|null $discount
 * @property-read int|null $tax_amount
 * @property-read int $total
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
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'sale_return_id' => 'integer',
            'product_id' => 'integer',
            'sale_item_id' => 'integer',
            'quantity' => 'integer',
            'price' => 'integer',
            'cost' => 'integer',
            'discount' => 'integer',
            'tax_amount' => 'integer',
            'total' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
