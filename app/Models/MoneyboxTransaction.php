<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\MoneyboxTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read int $id
 * @property-read int $moneybox_id
 * @property-read string $type
 * @property-read float $amount
 * @property-read float $balance_before
 * @property-read float $balance_after
 * @property-read int|null $transfer_to_moneybox_id
 * @property-read string $transactionable_type
 * @property-read int $transactionable_id
 * @property-read string|null $reference
 * @property-read string|null $notes
 * @property-read int|null $user_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Moneybox $moneybox
 * @property-read Moneybox|null $transferToMoneybox
 * @property-read User|null $user
 * @property-read Model $transactionable
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
     * @return BelongsTo<Moneybox, $this>
     */
    public function transferToMoneybox(): BelongsTo
    {
        return $this->belongsTo(Moneybox::class, 'transfer_to_moneybox_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'moneybox_id' => 'integer',
            'type' => 'string',
            'amount' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'transfer_to_moneybox_id' => 'integer',
            'transactionable_type' => 'string',
            'transactionable_id' => 'integer',
            'reference' => 'string',
            'notes' => 'string',
            'user_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
