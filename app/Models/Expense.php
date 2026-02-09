<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $expense_category_id
 * @property-read int|null $user_id
 * @property-read string $reference_no
 * @property-read int $amount
 * @property-read CarbonInterface $expense_date
 * @property-read string|null $description
 * @property-read string|null $document
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<ExpenseCategory, $this>
     */
    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'expense_category_id' => 'integer',
            'user_id' => 'integer',
            'reference_no' => 'string',
            'amount' => 'integer',
            'expense_date' => 'date',
            'description' => 'string',
            'document' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
