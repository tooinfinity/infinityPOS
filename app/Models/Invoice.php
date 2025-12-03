<?php

declare(strict_types=1);

namespace App\Models;

use App\QueryBuilders\InvoiceQueryBuilder;
use Carbon\CarbonInterface;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
 * @property-read string $status
 * @property-read string|null $notes
 * @property-read int $created_by
 * @property-read int|null $updated_by
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Sale $sale
 * @property-read Client|null $client
 * @property-read User $creator
 * @property-read User|null $updater
 * @property-read Collection<int, Payment> $payments
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
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'related_id')
            ->where('payments.type', 'sale');
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
            'status' => 'string',
            'notes' => 'string',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
