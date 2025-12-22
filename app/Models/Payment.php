<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentMethodEnum;
use Carbon\CarbonInterface;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read int $id
 * @property-read string|null $reference
 * @property-read int $amount
 * @property-read PaymentMethodEnum $method
 * @property-read string|null $notes
 * @property-read string|null $related_type
 * @property-read int|null $related_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Moneybox|null $moneybox
 * @property-read User $creator
 * @property-read User|null $updater
 */
final class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

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
     * @return BelongsTo<Moneybox, $this>
     */
    public function moneybox(): BelongsTo
    {
        return $this->belongsTo(Moneybox::class);
    }

    /**
     * @return HasMany<MoneyboxTransaction, $this>
     */
    public function moneyboxTransactions(): HasMany
    {
        return $this->hasMany(MoneyboxTransaction::class);
    }

    /**
     * Polymorphic related model (sale, purchase, invoice, expense, etc.).
     *
     * @return MorphTo<Model, $this>
     */
    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if payment is cash.
     */
    public function isCash(): bool
    {
        return $this->method->isCash();
    }

    /**
     * Check if payment is card.
     */
    public function isCard(): bool
    {
        return $this->method->isCard();
    }

    /**
     * Check if payment is transfer.
     */
    public function isTransfer(): bool
    {
        return $this->method->isTransfer();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'reference' => 'string',
            'amount' => 'integer',
            'method' => PaymentMethodEnum::class,
            'notes' => 'string',
            'related_type' => 'string',
            'related_id' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
