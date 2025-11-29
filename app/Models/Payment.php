<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentMethodEnum;
use Carbon\CarbonInterface;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read int $id
 * @property-read string $reference
 * @property-read string $payable_type
 * @property-read int $payable_id
 * @property-read float $amount
 * @property-read PaymentMethodEnum $method
 * @property-read int|null $moneybox_id
 * @property-read string|null $notes
 * @property-read int|null $user_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Model|null $payable
 * @property-read Moneybox|null $moneybox
 * @property-read User|null $user
 */
final class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    /**
     * @return MorphTo<Model, $this>
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if payment is cash.
     */
    public function isCash(): bool
    {
        return $this->method === PaymentMethodEnum::CASH;
    }

    /**
     * Check if payment is card.
     */
    public function isCard(): bool
    {
        return $this->method === PaymentMethodEnum::CARD;
    }

    /**
     * Check if payment is transfer.
     */
    public function isTransfer(): bool
    {
        return $this->method === PaymentMethodEnum::TRANSFER;
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'reference' => 'string',
            'payable_type' => 'string',
            'payable_id' => 'integer',
            'amount' => 'decimal:2',
            'method' => PaymentMethodEnum::class,
            'moneybox_id' => 'integer',
            'notes' => 'string',
            'user_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
