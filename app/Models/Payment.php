<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentTypeEnum;
use Carbon\CarbonInterface;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read string|null $reference
 * @property-read PaymentTypeEnum $type
 * @property-read int $amount
 * @property-read string $method
 * @property-read string|null $notes
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
     * @return BelongsTo<Moneybox, $this>
     */
    public function moneybox(): BelongsTo
    {
        return $this->belongsTo(Moneybox::class);
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
     * Check if payment is cash.
     */
    public function isCash(): bool
    {
        return $this->method === PaymentMethodEnum::CASH->value;
    }

    /**
     * Check if payment is card.
     */
    public function isCard(): bool
    {
        return $this->method === PaymentMethodEnum::CARD->value;
    }

    /**
     * Check if payment is transfer.
     */
    public function isTransfer(): bool
    {
        return $this->method === PaymentMethodEnum::TRANSFER->value;
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'reference' => 'string',
            'type' => PaymentTypeEnum::class,
            'amount' => 'integer',
            'method' => \App\Enums\PaymentMethodEnum::class,
            'notes' => 'string',
            'related_id' => 'integer',
            'moneybox_id' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
