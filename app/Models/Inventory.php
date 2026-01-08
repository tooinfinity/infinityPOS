<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\InventoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $store_id
 * @property-read int $product_id
 * @property-read int $total_quantity
 * @property-read CarbonInterface $updated_at
 */
final class Inventory extends Model
{
    /** @use HasFactory<InventoryFactory> */
    use HasFactory;

    /** @var bool */
    public $timestamps = false;

    /** @var string */
    protected $table = 'inventory';

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
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
            'store_id' => 'integer',
            'product_id' => 'integer',
            'total_quantity' => 'integer',
            'updated_at' => 'datetime',
        ];
    }
}
