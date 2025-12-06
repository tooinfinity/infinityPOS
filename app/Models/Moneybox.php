<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MoneyboxTransactionTypeEnum;
use Carbon\CarbonInterface;
use Database\Factories\MoneyboxFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $type
 * @property-read string|null $description
 * @property-read float $balance
 * @property-read string|null $bank_name
 * @property-read string|null $account_number
 * @property-read int|null $store_id
 * @property-read int $created_by
 * @property-read int|null $updated_by
 * @property-read bool $is_active
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Store|null $store
 * @property-read User $creator
 * @property-read User|null $updater
 * @property-read Collection<int, MoneyboxTransaction> $transactions
 * @property-read Collection<int, Payment> $payments
 * @property-read Collection<int, Expense> $expenses
 * @property-read Collection<int, MoneyboxTransaction> $incomingTransfers
 * @property-read Collection<int, MoneyboxTransaction> $outgoingTransfers
 */
final class Moneybox extends Model
{
    /** @use HasFactory<MoneyboxFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
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
     * @return HasMany<MoneyboxTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(MoneyboxTransaction::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return HasMany<Expense, $this>
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * @return HasMany<MoneyboxTransaction, $this>
     */
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(MoneyboxTransaction::class, 'transfer_to_id');
    }

    /**
     * @return HasMany<MoneyboxTransaction, $this>
     */
    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(MoneyboxTransaction::class, 'moneybox_id')
            ->where('type', MoneyboxTransactionTypeEnum::TRANSFER->value);
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'type' => 'string',
            'description' => 'string',
            'balance' => 'decimal:2',
            'bank_name' => 'string',
            'account_number' => 'string',
            'store_id' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
