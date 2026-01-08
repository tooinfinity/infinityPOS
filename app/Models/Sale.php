<?php

declare(strict_types=1);

namespace App\Models;

use App\Collections\SaleCollection;
use App\Enums\PaymentMethodEnum;
use App\Enums\SaleStatusEnum;
use Carbon\CarbonInterface;
use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read int $store_id
 * @property-read int|null $customer_id
 * @property-read int|null $register_session_id
 * @property-read string $invoice_number
 * @property-read CarbonInterface $sale_date
 * @property-read int $subtotal
 * @property-read int $discount_amount
 * @property-read int $total_amount
 * @property-read PaymentMethodEnum $payment_method
 * @property-read int $amount_paid
 * @property-read int $change_given
 * @property-read SaleStatusEnum $status
 * @property-read string|null $notes
 * @property-read int $cashier_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Sale extends Model
{
    /** @use HasFactory<SaleFactory> */
    use HasFactory;

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array<int, static>  $models
     * @return SaleCollection<int, static>
     */
    public function newCollection(array $models = []): SaleCollection
    {
        return new SaleCollection($models);
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
     * @return BelongsTo<RegisterSession, $this>
     */
    public function registerSession(): BelongsTo
    {
        return $this->belongsTo(RegisterSession::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    /**
     * @return HasMany<SaleItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * @return HasMany<SalePayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    /**
     * @return HasMany<SaleReturn, $this>
     */
    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    /**
     * Calculate total profit from all items.
     */
    public function getTotalProfit(): int
    {
        $sum = $this->items->sum('profit');

        return is_numeric($sum) ? (int) $sum : 0;
    }

    /**
     * Check if sale is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === SaleStatusEnum::COMPLETED;
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
            'customer_id' => 'integer',
            'register_session_id' => 'integer',
            'invoice_number' => 'string',
            'sale_date' => 'datetime',
            'subtotal' => 'integer',
            'discount_amount' => 'integer',
            'total_amount' => 'integer',
            'payment_method' => PaymentMethodEnum::class,
            'amount_paid' => 'integer',
            'change_given' => 'integer',
            'status' => SaleStatusEnum::class,
            'notes' => 'string',
            'cashier_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
