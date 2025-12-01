<?php

declare(strict_types=1);

namespace App\QueryBuilders;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<Product>
 */
final class ProductQueryBuilder extends Builder
{
    public function active(): self
    {
        return $this->where('is_active', true);
    }

    public function inactive(): self
    {
        return $this->where('is_active', false);
    }

    public function lowStock(): self
    {
        return $this->whereRaw(
            'COALESCE((SELECT SUM(quantity) FROM store_stock WHERE product_id = products.id), 0) <= alert_quantity'
        );
    }

    public function withBatches(): self
    {
        return $this->where('has_batches', true);
    }

    public function inCategory(int $categoryId): self
    {
        return $this->where('category_id', $categoryId);
    }

    public function searchByName(string $search): self
    {
        return $this->where('name', 'like', sprintf('%%%s%%', $search));
    }

    public function withStockInStore(int $storeId): self
    {
        return $this->whereHas('stores', function (Builder $query) use ($storeId): void {
            $query->where('store_id', $storeId)
                ->where('quantity', '>', 0);
        });
    }
}
