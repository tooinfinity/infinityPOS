<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\ReturnItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $return_id
 * @property-read int|null $sale_item_id
 * @property-read int|null $invoice_item_id
 * @property-read int $product_id
 * @property-read int $quantity
 * @property-read int $unit_price
 * @property-read int $unit_cost
 * @property-read int $subtotal
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class ReturnItem extends Model
{
    /** @use HasFactory<ReturnItemFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<SaleReturn, $this>
     */
    public function return(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class, 'return_id');
    }

    /**
     * @return BelongsTo<SaleItem, $this>
     */
    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    /**
     * @return BelongsTo<InvoiceItem, $this>
     */
    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'return_id' => 'integer',
            'sale_item_id' => 'integer',
            'invoice_item_id' => 'integer',
            'product_id' => 'integer',
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'unit_cost' => 'integer',
            'subtotal' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
