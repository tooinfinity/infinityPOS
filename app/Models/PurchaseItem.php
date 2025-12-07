<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\PurchaseItemFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read int $quantity
 * @property-read int $cost
 * @property-read int|null $discount
 * @property-read int|null $tax_amount
 * @property-read int $total
 * @property-read string|null $batch_number
 * @property-read CarbonInterface|null $expiry_date
 * @property-read int|null $remaining_quantity
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Purchase $purchase
 * @property-read Product $product
 * @property-read Collection<int, PurchaseReturnItem> $returnItems
 */
final class PurchaseItem extends Model
{
    /** @use HasFactory<PurchaseItemFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Purchase, $this>
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return HasMany<PurchaseReturnItem, $this>
     */
    public function returnItems(): HasMany
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
            'purchase_id' => 'integer',
            'product_id' => 'integer',
            'quantity' => 'integer',
            'cost' => 'integer',
            'discount' => 'integer',
            'tax_amount' => 'integer',
            'total' => 'integer',
            'batch_number' => 'string',
            'expiry_date' => 'datetime',
            'remaining_quantity' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
