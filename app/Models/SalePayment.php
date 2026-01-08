<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentMethodEnum;
use Carbon\CarbonInterface;
use Database\Factories\SalePaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $sale_id
 * @property-read PaymentMethodEnum $payment_method
 * @property-read int $amount
 * @property-read string|null $reference_number
 * @property-read CarbonInterface $created_at
 */
final class SalePayment extends Model
{
    /** @use HasFactory<SalePaymentFactory> */
    use HasFactory;

    /** @var bool */
    public $timestamps = false;

    /**
     * @return BelongsTo<Sale, $this>
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
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
            'payment_method' => PaymentMethodEnum::class,
            'amount' => 'integer',
            'reference_number' => 'string',
            'created_at' => 'datetime',
        ];
    }
}
