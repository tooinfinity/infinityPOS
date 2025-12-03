<?php

declare(strict_types=1);

namespace App\Models;

use App\QueryBuilders\ExpenseQueryBuilder;
use Carbon\CarbonInterface;
use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read float $amount
 * @property-read string|null $description
 * @property-read int|null $category_id
 * @property-read int|null $store_id
 * @property-read int|null $moneybox_id
 * @property-read int $created_by
 * @property-read int|null $updated_by
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Category|null $category
 * @property-read Store|null $store
 * @property-read Moneybox|null $moneybox
 * @property-read User $creator
 * @property-read User|null $updater
 */
#[UseEloquentBuilder(ExpenseQueryBuilder::class)]
final class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
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
    public function moneyboxTransactions(): HasMany
    {
        return $this->hasMany(MoneyboxTransaction::class, 'expense_id');
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'amount' => 'decimal:2',
            'description' => 'string',
            'category_id' => 'integer',
            'store_id' => 'integer',
            'moneybox_id' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
