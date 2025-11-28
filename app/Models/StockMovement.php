<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StockMovementTypeEnum;
use Carbon\CarbonInterface;
use Database\Factories\StockMovementFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read int $id
 * @property-read int $product_id
 * @property-read int $store_id
 * @property-read float $quantity
 * @property-read StockMovementTypeEnum $type
 * @property-read string|null $source_type
 * @property-read int|null $source_id
 * @property-read string|null $batch_number
 * @property-read string|null $notes
 * @property-read int|null $user_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Product $product
 * @property-read Store $store
 * @property-read User|null $user
 * @property-read Model|null $source
 */
final class StockMovement extends Model
{
    /** @use HasFactory<StockMovementFactory> */
    use HasFactory;

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

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if stock movement is incoming.
     */
    /**
     * Check if movement is incoming (increases stock).
     */
    public function isIncoming(): bool
    {
        return $this->type->isIncoming();
    }

    /**
     * @return Attribute<float, never>
     */
    protected function effectiveQuantity(): Attribute
    {
        return Attribute::make(
            get: fn (): float => $this->isIncoming() ? $this->quantity : -$this->quantity
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'product_id' => 'integer',
            'store_id' => 'integer',
            'quantity' => 'decimal:2',
            'type' => StockMovementTypeEnum::class,
            'source_type' => 'string',
            'source_id' => 'integer',
            'batch_number' => 'string',
            'notes' => 'string',
            'user_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
