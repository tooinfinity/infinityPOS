<?php

declare(strict_types=1);

namespace App\Models;

use App\Collections\PurchaseCollection;
use App\Enums\PaymentMethodEnum;
use App\Enums\PurchaseStatusEnum;
use Carbon\CarbonInterface;
use Database\Factories\PurchaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read int $store_id
 * @property-read int|null $supplier_id
 * @property-read string $reference_number
 * @property-read string|null $invoice_number
 * @property-read CarbonInterface $purchase_date
 * @property-read int $total_cost
 * @property-read int $paid_amount
 * @property-read PurchaseStatusEnum $payment_status
 * @property-read PaymentMethodEnum|null $payment_method
 * @property-read string|null $notes
 * @property-read int $created_by
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Purchase extends Model
{
    /** @use HasFactory<PurchaseFactory> */
    use HasFactory;

    /**
     * @param  array<int, static>  $models
     * @return PurchaseCollection<int, static>
     */
    public function newCollection(array $models = []): PurchaseCollection
    {
        return new PurchaseCollection($models);
    }

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<PurchaseItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    // Helper methods

    /**
     * Get the total cost in dollars.
     */
    public function getTotalCostInDollars(): float
    {
        return $this->total_cost / 100;
    }

    /**
     * Get the outstanding balance in cents.
     */
    public function getOutstandingBalance(): int
    {
        return $this->total_cost - $this->paid_amount;
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
            'supplier_id' => 'integer',
            'reference_number' => 'string',
            'invoice_number' => 'string',
            'purchase_date' => 'date',
            'total_cost' => 'integer',
            'paid_amount' => 'integer',
            'payment_status' => PurchaseStatusEnum::class,
            'payment_method' => PaymentMethodEnum::class,
            'notes' => 'string',
            'created_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
