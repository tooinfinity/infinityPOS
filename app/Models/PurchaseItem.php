<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\PurchaseItemFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read int $purchase_id
 * @property-read int $product_id
 * @property-read float $quantity
 * @property-read float $cost
 * @property-read float $total
 * @property-read string|null $batch_number
 * @property-read CarbonImmutable|null $expiry_date
 * @property-read float|null $remaining_quantity
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
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
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'purchase_id' => 'integer',
            'product_id' => 'integer',
            'quantity' => 'decimal:2',
            'cost' => 'decimal:2',
            'total' => 'decimal:2',
            'batch_number' => 'string',
            'expiry_date' => 'date',
            'remaining_quantity' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
