<?php

declare(strict_types=1);

namespace App\Collections;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends Collection<int, Product>
 */
final class ProductCollection extends Collection
{
    /**
     * Get only active products.
     *
     * @return self<int, Product>
     */
    public function active(): self
    {
        return $this->filter(fn (Product $product): bool => $product->is_active);
    }

    /**
     * Get products by category.
     *
     * @return self<int, Product>
     */
    public function byCategory(int $categoryId): self
    {
        return $this->filter(fn (Product $product): bool => $product->category_id === $categoryId);
    }

    /**
     * Get low stock products for a given store.
     *
     * @return self<int, Product>
     */
    public function lowStock(int $storeId): self
    {
        return $this->filter(fn (Product $product): bool => $product->isLowStock($storeId));
    }

    /**
     * Get total value of all products at selling price.
     */
    public function totalSellingValue(): int
    {
        $sum = $this->sum('selling_price');

        return is_numeric($sum) ? (int) $sum : 0;
    }
}
