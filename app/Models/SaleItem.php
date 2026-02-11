<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\SaleItemFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $sale_id
 * @property-read int $product_id
 * @property-read int|null $batch_id
 * @property-read int $quantity
 * @property-read int $unit_price
 * @property-read int $unit_cost
 * @property-read int $subtotal
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
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
     * @return BelongsTo<Batch, $this>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
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
            'batch_id' => 'integer',
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'unit_cost' => 'integer',
            'subtotal' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return Attribute<int, null>
     */
    protected function profit(): Attribute
    {
        return Attribute::make(
            get: fn (): int => ($this->unit_price - $this->unit_cost) * $this->quantity,
        );
    }
}
