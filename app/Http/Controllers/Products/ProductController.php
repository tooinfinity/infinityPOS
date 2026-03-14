<?php

declare(strict_types=1);

namespace App\Http\Controllers\Products;

use App\Actions\Product\CreateProduct;
use App\Actions\Product\DeleteProduct;
use App\Actions\Product\UpdateProduct;
use App\Data\Product\ProductData;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class ProductController
{
    public function index(): Response
    {
        /** @var array{
         *     search?: string|null,
         *     category_id?: int|null,
         *     brand_id?: int|null,
         *     track_inventory?: bool|string|null,
         *     sort?: string|null,
         *     direction?: string|null
         * } $filters
         */
        $filters = request()->only([
            'search',
            'category_id',
            'brand_id',
            'track_inventory',
            'sort',
            'direction',
        ]);
        $perPage = request()->integer('per_page');

        return Inertia::render('products/index', [
            'products' => Product::query()->paginateWithFilters($filters, $perPage),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name']),
            'units' => Unit::query()->orderBy('name')->get(['id', 'name', 'short_name']),
            'filters' => $filters,
            'perPage' => $perPage,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(ProductData $data, CreateProduct $action): RedirectResponse
    {
        $product = $action->handle($data);

        return to_route('products.show', $product)
            ->with('success', "Product '$product->name' created.");
    }

    public function show(Product $product): Response
    {
        $product->load(['unit', 'category', 'brand', 'batches.warehouse']);

        return Inertia::render('products/show', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name']),
            'units' => Unit::query()->orderBy('name')->get(['id', 'name', 'short_name']),
            'stockByWarehouse' => (Product::query()->whereKey($product->id))->getStockByWarehouse(),
            'recentMovements' => (Product::query()->whereKey($product->id))->getRecentMovements(),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function update(
        Product $product,
        ProductData $data,
        UpdateProduct $action,
    ): RedirectResponse {
        $action->handle($product, $data);

        return to_route('products.show', $product)
            ->with('success', "Product '$product->name' updated.");
    }

    /**
     * @throws Throwable
     */
    public function destroy(Product $product, DeleteProduct $action): RedirectResponse
    {
        $action->handle($product);

        return to_route('products.index')
            ->with('success', 'Product deleted.');
    }
}
