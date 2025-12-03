<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CategoryTypeEnum;
use App\QueryBuilders\CategoryQueryBuilder;
use Carbon\CarbonInterface;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $code
 * @property-read string $type
 * @property-read bool $is_active
 * @property-read int $created_by
 * @property-read int|null $updated_by
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Collection<int, Product> $products
 * @property-read Collection<int, Expense> $expenses
 * @property-read User $creator
 * @property-read User|null $updater
 */
#[UseEloquentBuilder(CategoryQueryBuilder::class)]
final class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @return HasMany<Expense, $this>
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
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
     * Check if category is for products.
     */
    public function isProductCategory(): bool
    {
        return $this->type === CategoryTypeEnum::PRODUCT->value;
    }

    /**
     * Check if category is for expenses.
     */
    public function isExpenseCategory(): bool
    {
        return $this->type === CategoryTypeEnum::EXPENSE->value;
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'code' => 'string',
            'type' => 'string',
            'is_active' => 'boolean',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
