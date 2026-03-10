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
        $products = Product::withInactive()
            ->with(['category', 'brand', 'unit'])
            ->withStockQuantity()
            ->latest()
            ->paginate(25);

        return Inertia::render('products/index', [
            'products' => $products,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('products/create', [
            'categories' => Category::query()->select('id', 'name')->get(),
            'brands' => Brand::query()->select('id', 'name')->get(),
            'units' => Unit::query()->select('id', 'name', 'short_name')->get(),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(ProductData $data, CreateProduct $action): RedirectResponse
    {
        $product = $action->handle($data);

        return to_route('products.show', $product)
            ->with('success', "Product '{$product->name}' created.");
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

    public function edit(Product $product): Response
    {
        $product->load(['category', 'brand', 'unit']);

        return Inertia::render('products/edit', [
            'product' => $product,
            'categories' => Category::query()->select('id', 'name')->get(),
            'brands' => Brand::query()->select('id', 'name')->get(),
            'units' => Unit::query()->select('id', 'name', 'short_name')->get(),
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
            ->with('success', "Product '{$product->name}' updated.");
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
