<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\InvoicePaymentProgressCast;
use App\Casts\InvoiceRemainingAmountCast;
use App\Enums\InvoiceStatusEnum;
use App\QueryBuilders\InvoiceQueryBuilder;
use Carbon\CarbonInterface;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property-read int $id
 * @property-read string $reference
 * @property-read int $sale_id
 * @property-read int|null $client_id
 * @property-read CarbonInterface $issued_at
 * @property-read CarbonInterface|null $due_at
 * @property-read CarbonInterface|null $paid_at
 * @property-read float $subtotal
 * @property-read float $discount
 * @property-read float $tax
 * @property-read float $total
 * @property-read float $paid
 * @property-read InvoiceStatusEnum $status
 * @property-read string|null $notes
 * @property-read int|null $user_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Sale $sale
 * @property-read Client|null $client
 * @property-read User|null $user
 * @property-read Collection<int, Payment> $payments
 * @property-read float $remaining_amount
 * @property-read float $payment_progress
 */
#[UseEloquentBuilder(InvoiceQueryBuilder::class)]
final class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Sale, $this>
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphMany<Payment, $this>
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'reference' => 'string',
            'sale_id' => 'integer',
            'client_id' => 'integer',
            'issued_at' => 'date',
            'due_at' => 'date',
            'paid_at' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'paid' => 'decimal:2',
            'status' => InvoiceStatusEnum::class,
            'notes' => 'string',
            'user_id' => 'integer',
            'remaining_amount' => InvoiceRemainingAmountCast::class,
            'payment_progress' => InvoicePaymentProgressCast::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
