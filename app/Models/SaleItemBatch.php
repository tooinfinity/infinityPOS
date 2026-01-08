<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\SaleItemBatchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $sale_item_id
 * @property-read int $inventory_batch_id
 * @property-read int $quantity_used
 * @property-read int $unit_cost
 * @property-read CarbonInterface $created_at
 */
final class SaleItemBatch extends Model
{
    /** @use HasFactory<SaleItemBatchFactory> */
    use HasFactory;

    /** @var bool */
    public $timestamps = false;

    /**
     * @return BelongsTo<SaleItem, $this>
     */
    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    /**
     * @return BelongsTo<InventoryBatch, $this>
     */
    public function inventoryBatch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class);
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
            'sale_item_id' => 'integer',
            'inventory_batch_id' => 'integer',
            'quantity_used' => 'integer',
            'unit_cost' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}
