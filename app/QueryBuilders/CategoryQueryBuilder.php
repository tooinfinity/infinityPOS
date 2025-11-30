<?php

declare(strict_types=1);

namespace App\QueryBuilders;

use App\Enums\CategoryTypeEnum;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<Category>
 */
final class CategoryQueryBuilder extends Builder
{
    public function active(): self
    {
        return $this->where('is_active', true);
    }

    public function inactive(): self
    {
        return $this->where('is_active', false);
    }

    public function ofType(CategoryTypeEnum $type): self
    {
        return $this->where('type', $type);
    }

    public function forProducts(): self
    {
        return $this->where('type', CategoryTypeEnum::PRODUCT);
    }

    public function forExpenses(): self
    {
        return $this->where('type', CategoryTypeEnum::EXPENSE);
    }

    public function withProductCount(): self
    {
        return $this->withCount('products');
    }

    public function withExpenseCount(): self
    {
        return $this->withCount('expenses');
    }

    public function withRelatedCount(): self
    {
        return $this->withCount(['products', 'expenses']);
    }

    public function hasProducts(): self
    {
        return $this->has('products');
    }

    public function hasExpenses(): self
    {
        return $this->has('expenses');
    }

    public function withoutProducts(): self
    {
        return $this->doesntHave('products');
    }

    public function withoutExpenses(): self
    {
        return $this->doesntHave('expenses');
    }

    public function empty(): self
    {
        return $this->doesntHave('products')
            ->doesntHave('expenses');
    }

    public function searchByName(string $search): self
    {
        return $this->where('name', 'like', "%{$search}%");
    }

    public function searchByCode(string $search): self
    {
        return $this->where('code', 'like', "%{$search}%");
    }

    public function search(string $search): self
    {
        return $this->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        });
    }

    public function orderByProductCount(string $direction = 'desc'): self
    {
        return $this->withCount('products')
            ->orderBy('products_count', $direction);
    }

    public function orderByExpenseCount(string $direction = 'desc'): self
    {
        return $this->withCount('expenses')
            ->orderBy('expenses_count', $direction);
    }
}
