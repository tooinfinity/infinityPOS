<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RegisterSessionStatusEnum;
use Carbon\CarbonInterface;
use Database\Factories\RegisterSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read int $cash_register_id
 * @property-read int $opened_by
 * @property-read int|null $closed_by
 * @property-read CarbonInterface $opening_time
 * @property-read CarbonInterface|null $closing_time
 * @property-read int $opening_balance
 * @property-read int|null $expected_cash
 * @property-read int|null $actual_cash
 * @property-read int|null $difference
 * @property-read string|null $notes
 * @property-read RegisterSessionStatusEnum $status
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class RegisterSession extends Model
{
    /** @use HasFactory<RegisterSessionFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<CashRegister, $this>
     */
    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * @return HasMany<Sale, $this>
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * @return HasMany<CashTransaction, $this>
     */
    public function cashTransactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class);
    }

    /**
     * @return HasMany<Expense, $this>
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Calculate total cash variance.
     */
    public function getCashVariance(): int
    {
        return $this->difference ?? 0;
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
            'cash_register_id' => 'integer',
            'opened_by' => 'integer',
            'closed_by' => 'integer',
            'opening_time' => 'datetime',
            'closing_time' => 'datetime',
            'opening_balance' => 'integer',
            'expected_cash' => 'integer',
            'actual_cash' => 'integer',
            'difference' => 'integer',
            'notes' => 'string',
            'status' => RegisterSessionStatusEnum::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
