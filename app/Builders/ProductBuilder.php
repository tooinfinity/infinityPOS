<?php

declare(strict_types=1);

namespace App\Builders;

use App\Models\Batch;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

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
        return $this->when($search, fn (self $q): self => $q->where(fn (self $q): self => $q
            ->where('name', 'like', "%$search%")
            ->orWhere('sku', 'like', "%$search%")
            ->orWhere('barcode', 'like', "%$search%")
        ));
    }

    public function category(?int $categoryId): self
    {
        return $this->when($categoryId, fn (self $q): self => $q->where('category_id', $categoryId));
    }

    public function brand(?int $brandId): self
    {
        return $this->when($brandId, fn (self $q): self => $q->where('brand_id', $brandId));
    }

    public function tracked(?bool $isTracked = true): self
    {
        return $this->when($isTracked !== null, fn (self $q): self => $q->where('track_inventory', $isTracked));
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
     *     track_inventory?: bool|string|null,
     *     sort?: string|null,
     *     direction?: 'asc'|'desc'|string|null
     * } $filters
     */
    public function applyFilters(array $filters): self
    {
        $search = $filters['search'] ?? null;

        $categoryId = $filters['category_id'] ?? null;

        $brandId = $filters['brand_id'] ?? null;

        $track_inventory = isset($filters['track_inventory'])
            ? filter_var($filters['track_inventory'], FILTER_VALIDATE_BOOLEAN)
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
            ->tracked($track_inventory)
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
     *      track_inventory?: bool|string|null,
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

    /**
     * @return Collection<int, Batch>
     */
    public function getStockByWarehouse(): Collection
    {
        return Batch::query()
            ->join('warehouses', 'batches.warehouse_id', '=', 'warehouses.id')
            ->whereColumn('batches.product_id', 'products.id')
            ->groupBy('batches.warehouse_id', 'warehouses.name')
            ->select('batches.warehouse_id', 'warehouses.name as warehouse_name')
            ->selectRaw('COALESCE(SUM(batches.quantity), 0) as total_quantity')
            ->orderBy('warehouses.name')
            ->get();
    }

    /**
     * @return Collection<int, StockMovement>
     */
    public function getRecentMovements(int $limit = 20): Collection
    {
        /** @var Collection<int, StockMovement> */
        return StockMovement::query()
            ->whereColumn('stock_movements.product_id', 'products.id')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
