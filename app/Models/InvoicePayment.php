<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentMethodEnum;
use Carbon\CarbonInterface;
use Database\Factories\InvoicePaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $invoice_id
 * @property-read CarbonInterface $payment_date
 * @property-read int $amount
 * @property-read PaymentMethodEnum $payment_method
 * @property-read string|null $reference_number
 * @property-read string|null $notes
 * @property-read int $recorded_by
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class InvoicePayment extends Model
{
    /** @use HasFactory<InvoicePaymentFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
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
            'invoice_id' => 'integer',
            'payment_date' => 'date',
            'amount' => 'integer',
            'payment_method' => PaymentMethodEnum::class,
            'reference_number' => 'string',
            'notes' => 'string',
            'recorded_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
