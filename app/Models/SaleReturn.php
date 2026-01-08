<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RefundMethodEnum;
use Carbon\CarbonInterface;
use Database\Factories\SaleReturnFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read int|null $sale_id
 * @property-read int|null $invoice_id
 * @property-read int $store_id
 * @property-read int|null $customer_id
 * @property-read string $return_number
 * @property-read CarbonInterface $return_date
 * @property-read int $total_amount
 * @property-read RefundMethodEnum $refund_method
 * @property-read string|null $reason
 * @property-read int $processed_by
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class SaleReturn extends Model
{
    /** @use HasFactory<SaleReturnFactory> */
    use HasFactory;

    /** @var string */
    protected $table = 'returns';

    /**
     * @return BelongsTo<Sale, $this>
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * @return HasMany<ReturnItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
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
            'sale_id' => 'integer',
            'invoice_id' => 'integer',
            'store_id' => 'integer',
            'customer_id' => 'integer',
            'return_number' => 'string',
            'return_date' => 'datetime',
            'total_amount' => 'integer',
            'refund_method' => RefundMethodEnum::class,
            'reason' => 'string',
            'processed_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
