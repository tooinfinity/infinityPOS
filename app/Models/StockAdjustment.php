<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StockAdjustmentTypeEnum;
use Carbon\CarbonInterface;
use Database\Factories\StockAdjustmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $store_id
 * @property-read int $product_id
 * @property-read StockAdjustmentTypeEnum $adjustment_type
 * @property-read int $quantity
 * @property-read int|null $unit_cost
 * @property-read int|null $total_cost
 * @property-read string $reason
 * @property-read int $adjusted_by
 * @property-read CarbonInterface $created_at
 */
final class StockAdjustment extends Model
{
    /** @use HasFactory<StockAdjustmentFactory> */
    use HasFactory;

    /** @var bool */
    public $timestamps = false;

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
     * @return BelongsTo<User, $this>
     */
    public function adjuster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    public function isDecrease(): bool
    {
        return $this->quantity < 0;
    }

    public function isIncrease(): bool
    {
        return $this->quantity > 0;
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
            'adjustment_type' => StockAdjustmentTypeEnum::class,
            'quantity' => 'integer',
            'unit_cost' => 'integer',
            'total_cost' => 'integer',
            'reason' => 'string',
            'adjusted_by' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}
