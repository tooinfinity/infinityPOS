<?php

declare(strict_types=1);

namespace App\Http\Controllers\Products;

use App\Actions\Products\CreateProduct;
use App\Actions\Products\DeleteProduct;
use App\Actions\Products\UpdateProduct;
use App\Data\Products\CreateProductData;
use App\Data\Products\ProductData;
use App\Data\Products\UpdateProductData;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class ProductController
{
    public function index(): Response
    {
        $products = Product::query()
            ->with(['category', 'brand', 'unit'])
            ->latest()
            ->paginate(50);

        return Inertia::render('products/index', [
            'products' => ProductData::collect($products),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('products/create', [
            'categories' => Category::query()->latest()->get(),
            'brands' => Brand::query()->latest()->get(),
            'units' => Unit::query()->latest()->get(),
        ]);
    }

    public function store(CreateProductData $data, CreateProduct $action): RedirectResponse
    {
        $action->handle($data);

        return to_route('products.index');
    }

    public function show(Product $product): Response
    {
        $product->load(['category', 'brand', 'unit', 'creator']);

        return Inertia::render('products/show', [
            'product' => ProductData::from($product),
        ]);
    }

    public function edit(Product $product): Response
    {
        $product->load(['category', 'brand', 'unit']);

        return Inertia::render('products/edit', [
            'product' => ProductData::from($product),
            'categories' => Category::query()->latest()->get(),
            'brands' => Brand::query()->latest()->get(),
            'units' => Unit::query()->latest()->get(),
        ]);
    }

    public function update(UpdateProductData $data, Product $product, UpdateProduct $action): RedirectResponse
    {
        $action->handle($product, $data);

        return back();
    }

    public function destroy(Product $product, DeleteProduct $action): RedirectResponse
    {
        $action->handle($product);

        return to_route('products.index');
    }
}
