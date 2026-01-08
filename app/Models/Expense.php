<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ExpenseCategoryEnum;
use Carbon\CarbonInterface;
use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $store_id
 * @property-read int|null $register_session_id
 * @property-read ExpenseCategoryEnum $expense_category
 * @property-read int $amount
 * @property-read string $description
 * @property-read CarbonInterface $expense_date
 * @property-read int $recorded_by
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * @return BelongsTo<RegisterSession, $this>
     */
    public function registerSession(): BelongsTo
    {
        return $this->belongsTo(RegisterSession::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
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
            'store_id' => 'integer',
            'register_session_id' => 'integer',
            'expense_category' => ExpenseCategoryEnum::class,
            'amount' => 'integer',
            'description' => 'string',
            'expense_date' => 'date',
            'recorded_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
