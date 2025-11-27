<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\PurchaseReturnItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $purchase_return_id
 * @property-read int $product_id
 * @property-read int|null $purchase_item_id
 * @property-read float $quantity
 * @property-read float $cost
 * @property-read float $total
 * @property-read string|null $batch_number
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read PurchaseReturn $purchaseReturn
 * @property-read Product $product
 * @property-read PurchaseItem|null $purchaseItem
 */
final class PurchaseReturnItem extends Model
{
    /** @use HasFactory<PurchaseReturnItemFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<PurchaseReturn, $this>
     */
    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<PurchaseItem, $this>
     */
    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'purchase_return_id' => 'integer',
            'product_id' => 'integer',
            'purchase_item_id' => 'integer',
            'quantity' => 'decimal:2',
            'cost' => 'decimal:2',
            'total' => 'decimal:2',
            'batch_number' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
