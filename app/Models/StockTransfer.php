<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StockTransferStatusEnum;
use Carbon\CarbonInterface;
use Database\Factories\StockTransferFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read string $reference
 * @property-read int $from_store_id
 * @property-read int $to_store_id
 * @property-read string $status
 * @property-read string|null $notes
 * @property-read int $created_by
 * @property-read int|null $updated_by
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Store $fromStore
 * @property-read Store $toStore
 * @property-read User $creator
 * @property-read User|null $updater
 * @property-read Collection<int, StockTransferItem> $items
 * @property-read Collection<int, StockMovement> $stockMovements
 */
final class StockTransfer extends Model
{
    /** @use HasFactory<StockTransferFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Store, $this>
     */
    public function fromStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'from_store_id');
    }

    /**
     * @return BelongsTo<Store, $this>
     */
    public function toStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'to_store_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @return HasMany<StockTransferItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'reference', 'reference')
            ->where('stock_movements.type', 'transfer');
    }

    /**
     * Check if transfer is pending.
     */
    public function isPending(): bool
    {
        return $this->status === StockTransferStatusEnum::PENDING->value;
    }

    /**
     * Check if transfer is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === StockTransferStatusEnum::COMPLETED->value;
    }

    /**
     * Check if transfer is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === StockTransferStatusEnum::CANCELLED->value;
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'reference' => 'string',
            'from_store_id' => 'integer',
            'to_store_id' => 'integer',
            'status' => 'string',
            'notes' => 'string',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
