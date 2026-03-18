<?php

declare(strict_types=1);

namespace App\Models;

use App\Builders\ExpenseCategoryBuilder;
use App\Models\Scopes\ActiveScope;
use Carbon\CarbonInterface;
use Database\Factories\ExpenseCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string|null $description
 * @property-read bool $is_active
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Collection<int, Expense> $expenses
 *
 * @method static ExpenseCategoryBuilder query()
 */
#[ScopedBy([ActiveScope::class])]
final class ExpenseCategory extends Model
{
    /** @use HasFactory<ExpenseCategoryFactory> */
    use HasFactory;

    public static function withInactive(): ExpenseCategoryBuilder
    {
        return self::query()->withoutGlobalScope(ActiveScope::class);
    }

    public function newEloquentBuilder(mixed $query): ExpenseCategoryBuilder
    {
        return new ExpenseCategoryBuilder($query);
    }

    /**
     * @return HasMany<Expense, $this>
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'description' => 'string',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
