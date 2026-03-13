<?php

declare(strict_types=1);

namespace App\Http\Controllers\Products;

use App\Actions\Product\CreateProduct;
use App\Actions\Product\DeleteProduct;
use App\Actions\Product\UpdateProduct;
use App\Data\Product\ProductData;
use App\Models\Product;
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
         *     is_tracked?: bool|string|null,
         *     sort?: string|null,
         *     direction?: string|null
         * } $filters
         */
        $filters = request()->only([
            'search',
            'category_id',
            'brand_id',
            'tracked',
            'low_stock',
            'out_of_stock',
        ]);
        $perPage = request()->integer('per_page');

        $product = Product::query()->paginateWithFilters($filters, $perPage);

        return Inertia::render('products/index', [
            'products' => $product,
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
        $product->load([
            'category',
            'brand',
            'unit',
            'batches.warehouse',
        ]);

        $product->loadCount([
            'purchaseItems',
            'saleItems',
            'stockMovements',
        ]);

        return Inertia::render('products/show', [
            'product' => $product,
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
