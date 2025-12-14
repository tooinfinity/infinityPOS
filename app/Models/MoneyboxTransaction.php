<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MoneyboxTransactionTypeEnum;
use Carbon\CarbonInterface;
use Database\Factories\MoneyboxTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read MoneyboxTransactionTypeEnum $type
 * @property-read int $amount
 * @property-read int $balance_after
 * @property-read string|null $reference
 * @property-read string|null $notes
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Moneybox $moneybox
 * @property-read Payment|null $payment
 * @property-read Expense|null $expense
 * @property-read Moneybox|null $transferTo
 * @property-read User $creator
 * @property-read User|null $updater
 */
final class MoneyboxTransaction extends Model
{
    /** @use HasFactory<MoneyboxTransactionFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Moneybox, $this>
     */
    public function moneybox(): BelongsTo
    {
        return $this->belongsTo(Moneybox::class);
    }

    /**
     * @return BelongsTo<Payment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * @return BelongsTo<Expense, $this>
     */
    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    /**
     * @return BelongsTo<Moneybox, $this>
     */
    public function transferTo(): BelongsTo
    {
        return $this->belongsTo(Moneybox::class, 'transfer_to_id');
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
     * Check if transaction is incoming.
     */
    public function isIncoming(): bool
    {
        return $this->type->isIn();
    }

    /**
     * Check if transaction is outgoing.
     */
    public function isOutgoing(): bool
    {
        return $this->type->isOut();
    }

    /**
     * Check if transaction is a transfer.
     */
    public function isTransfer(): bool
    {
        return $this->type->isTransfer();
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'moneybox_id' => 'integer',
            'type' => MoneyboxTransactionTypeEnum::class,
            'amount' => 'integer',
            'balance_after' => 'integer',
            'reference' => 'string',
            'notes' => 'string',
            'payment_id' => 'integer',
            'expense_id' => 'integer',
            'transfer_to_id' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
