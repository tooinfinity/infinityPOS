<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read string $id
 * @property-read string $reference
 * @property-read CarbonInterface $issued_at
 * @property-read CarbonInterface|null $due_at
 * @property-read CarbonInterface|null $paid_at
 * @property-read string $subtotal
 * @property-read string $discount
 * @property-read string $tax
 * @property-read string $total
 * @property-read string $paid
 * @property-read string $status
 * @property-read string|null $notes
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Sale $sale
 * @property-read Client|null $client
 * @property-read User $creator
 * @property-read User|null $updater
 * @property-read Collection<int, Payment> $payments
 */
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
            'id' => 'string',
            'reference' => 'string',
            'sale_id' => 'string',
            'client_id' => 'string',
            'issued_at' => 'date',
            'due_at' => 'datetime',
            'paid_at' => 'datetime',
            'subtotal' => 'string',
            'discount' => 'string',
            'tax' => 'string',
            'total' => 'string',
            'paid' => 'string',
            'status' => 'string',
            'notes' => 'string',
            'created_by' => 'string',
            'updated_by' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
