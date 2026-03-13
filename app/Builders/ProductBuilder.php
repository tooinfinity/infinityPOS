<?php

declare(strict_types=1);

namespace App\Builders;

use App\Models\Batch;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends Builder<Product>
 */
final class ProductBuilder extends Builder
{
    /** @var string[] SORTABLE */
    private const array SORTABLE = [
        'name',
        'sku',
        'price',
        'created_at',
    ];

    public function search(?string $search): self
    {
        return $this->when($search, fn ($q) => $q->where(fn ($q) => $q
            ->where('name', 'like', "%$search%")
            ->orWhere('sku', 'like', "%$search%")
            ->orWhere('barcode', 'like', "%$search%")
        ));
    }

    public function category(?int $categoryId): self
    {
        return $this->when($categoryId, fn ($q) => $q->where('category_id', $categoryId));
    }

    public function brand(?int $brandId): self
    {
        return $this->when($brandId, fn ($q) => $q->where('brand_id', $brandId));
    }

    public function tracked(?bool $isTracked = true): self
    {
        return $this->when($isTracked !== null, fn ($q) => $q->where('track_inventory', $isTracked));
    }

    public function lowStock(): self
    {
        return $this->whereRaw(
            '(SELECT COALESCE(SUM(quantity), 0) FROM batches WHERE batches.product_id = products.id) <= products.alert_quantity'
        )->where('track_inventory', true);
    }

    public function outOfStock(): self
    {
        return $this->whereRaw(
            '(SELECT COALESCE(SUM(quantity), 0) FROM batches WHERE batches.product_id = products.id) <= 0'
        )->where('track_inventory', true);
    }

    public function withStockQuantity(): self
    {
        return $this->addSelect([
            'stock_quantity' => Batch::query()->selectRaw('COALESCE(SUM(quantity), 0)')
                ->whereColumn('batches.product_id', 'products.id'),
        ]);
    }

    /**
     * @param array{
     *     search?: string|null,
     *     category_id?: int|null,
     *     brand_id?: int|null,
     *     is_tracked?: bool|string|null,
     *     sort?: string|null,
     *     direction?: 'asc'|'desc'|string|null
     * } $filters
     */
    public function applyFilters(array $filters): self
    {
        $search = $filters['search'] ?? null;

        $categoryId = isset($filters['category_id'])
            ? (int) $filters['category_id']
            : null;

        $brandId = isset($filters['brand_id'])
            ? (int) $filters['brand_id']
            : null;

        $isTracked = isset($filters['is_tracked'])
            ? filter_var($filters['is_tracked'], FILTER_VALIDATE_BOOLEAN)
            : null;

        $sort = in_array($filters['sort'] ?? null, self::SORTABLE, true)
            ? $filters['sort']
            : null;

        $direction = in_array($filters['direction'] ?? null, ['asc', 'desc'], true)
            ? $filters['direction']
            : 'asc';

        return $this
            ->search($search)
            ->category($categoryId)
            ->brand($brandId)
            ->tracked($isTracked)
            ->when(
                $sort,
                fn (self $q, string $col): self => $q->orderBy($col, $direction),
                fn (self $q): self => $q->latest()
            );
    }

    /**
     * @param array{
     *      search?: string|null,
     *      category_id?: int|null,
     *      brand_id?: int|null,
     *      is_tracked?: bool|string|null,
     *      sort?: string|null,
     *      direction?: 'asc'|'desc'|string|null
     *  } $filters
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginateWithFilters(array $filters, ?int $perPage = null): LengthAwarePaginator
    {
        return $this
            ->applyFilters($filters)
            ->with(['unit', 'category', 'brand'])
            ->paginate($perPage ?? 25)
            ->withQueryString();
    }
}
