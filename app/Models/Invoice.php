<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoicePaymentStatusEnum;
use Carbon\CarbonInterface;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read int $store_id
 * @property-read int $customer_id
 * @property-read string $invoice_number
 * @property-read CarbonInterface $invoice_date
 * @property-read CarbonInterface|null $due_date
 * @property-read int $subtotal
 * @property-read int $discount_amount
 * @property-read int $total_amount
 * @property-read int $paid_amount
 * @property-read InvoicePaymentStatusEnum $payment_status
 * @property-read string|null $notes
 * @property-read string|null $terms
 * @property-read int $created_by
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

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
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<InvoiceItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * @return HasMany<InvoicePayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    /**
     * @return HasMany<SaleReturn, $this>
     */
    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    // Helper methods

    /**
     * Get the outstanding balance in cents.
     */
    public function getOutstandingBalance(): int
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Check if invoice is fully paid.
     */
    public function isFullyPaid(): bool
    {
        return $this->payment_status === InvoicePaymentStatusEnum::PAID;
    }

    /**
     * Check if invoice is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->payment_status === InvoicePaymentStatusEnum::OVERDUE ||
               ($this->due_date && $this->due_date->isPast() && ! $this->isFullyPaid());
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
            'invoice_number' => 'string',
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'integer',
            'discount_amount' => 'integer',
            'total_amount' => 'integer',
            'paid_amount' => 'integer',
            'payment_status' => InvoicePaymentStatusEnum::class,
            'notes' => 'string',
            'terms' => 'string',
            'created_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
