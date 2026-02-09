<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StockMovementTypeEnum;
use Carbon\CarbonInterface;
use Database\Factories\StockMovementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read int $id
 * @property-read int $warehouse_id
 * @property-read int $product_id
 * @property-read int|null $batch_id
 * @property-read int|null $user_id
 * @property-read StockMovementTypeEnum $type
 * @property-read int $quantity
 * @property-read int $previous_quantity
 * @property-read int $current_quantity
 * @property-read string $reference_type
 * @property-read int $reference_id
 * @property-read string $note
 * @property-read CarbonInterface $created_at
 */
final class StockMovement extends Model
{
    /** @use HasFactory<StockMovementFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return BelongsTo<Warehouse, $this>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'warehouse_id' => 'integer',
            'product_id' => 'integer',
            'batch_id' => 'integer',
            'user_id' => 'integer',
            'type' => StockMovementTypeEnum::class,
            'quantity' => 'integer',
            'previous_quantity' => 'integer',
            'current_quantity' => 'integer',
            'reference_type' => 'string',
            'reference_id' => 'integer',
            'note' => 'string',
            'created_at' => 'datetime',
        ];
    }
}
