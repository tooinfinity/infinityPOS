<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\StockTransferItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $quantity
 * @property-read string|null $batch_number
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read StockTransfer $stockTransfer
 * @property-read Product $product
 */
final class StockTransferItem extends Model
{
    /** @use HasFactory<StockTransferItemFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<StockTransfer, $this>
     */
    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'stock_transfer_id' => 'integer',
            'product_id' => 'integer',
            'quantity' => 'integer',
            'batch_number' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
