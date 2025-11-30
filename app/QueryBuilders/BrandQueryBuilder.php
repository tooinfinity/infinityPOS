<?php

declare(strict_types=1);

namespace App\QueryBuilders;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<Brand>
 */
final class BrandQueryBuilder extends Builder
{
    public function active(): self
    {
        return $this->where('is_active', true);
    }

    public function inactive(): self
    {
        return $this->where('is_active', false);
    }

    public function withProductCount(): self
    {
        return $this->withCount('products');
    }

    public function withActiveProducts(): self
    {
        return $this->with(['products' => function (Builder $query): void {
            $query->where('is_active', true);
        }]);
    }

    public function hasProducts(): self
    {
        return $this->has('products');
    }

    public function withoutProducts(): self
    {
        return $this->doesntHave('products');
    }

    public function searchByName(string $search): self
    {
        return $this->where('name', 'like', sprintf('%%%s%%', $search));
    }

    public function orderByProductCount(string $direction = 'desc'): self
    {
        return $this->withCount('products')
            ->orderBy('products_count', $direction);
    }

    public function popular(int $limit = 10): self
    {
        return $this->withCount('products')
            ->orderBy('products_count', 'desc')
            ->limit($limit);
    }
}
