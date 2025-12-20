<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\InventoryLayerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $product_id
 * @property-read int $store_id
 * @property-read string|null $batch_number
 * @property-read CarbonInterface|null $expiry_date
 * @property-read int $unit_cost
 * @property-read int $received_qty
 * @property int $remaining_qty
 * @property-read CarbonInterface $received_at
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class InventoryLayer extends Model
{
    /** @use HasFactory<InventoryLayerFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    public $casts = [
        'product_id' => 'integer',
        'store_id' => 'integer',
        'batch_number' => 'string',
        'expiry_date' => 'date',
        'unit_cost' => 'integer',
        'received_qty' => 'integer',
        'remaining_qty' => 'integer',
        'received_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
