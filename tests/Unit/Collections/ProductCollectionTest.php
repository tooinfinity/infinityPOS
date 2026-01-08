<?php

declare(strict_types=1);

use App\Collections\ProductCollection;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Store;

test('active returns only active products', function (): void {
    $activeProduct1 = Product::factory()->create(['is_active' => true]);
    $activeProduct2 = Product::factory()->create(['is_active' => true]);
    $inactiveProduct = Product::factory()->create(['is_active' => false]);

    $collection = new ProductCollection([$activeProduct1, $inactiveProduct, $activeProduct2]);

    $result = $collection->active();

    expect($result)
        ->toBeInstanceOf(ProductCollection::class)
        ->toHaveCount(2)
        ->and($result->pluck('id')->toArray())
        ->toBe([$activeProduct1->id, $activeProduct2->id]);
});

test('active returns empty collection when no active products', function (): void {
    $inactiveProduct1 = Product::factory()->create(['is_active' => false]);
    $inactiveProduct2 = Product::factory()->create(['is_active' => false]);

    $collection = new ProductCollection([$inactiveProduct1, $inactiveProduct2]);

    expect($collection->active())->toHaveCount(0);
});

test('active returns all products when all are active', function (): void {
    $activeProduct1 = Product::factory()->create(['is_active' => true]);
    $activeProduct2 = Product::factory()->create(['is_active' => true]);

    $collection = new ProductCollection([$activeProduct1, $activeProduct2]);

    expect($collection->active())->toHaveCount(2);
});

test('by category filters products by category id', function (): void {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    $product1 = Product::factory()->create(['category_id' => $category1->id]);
    $product2 = Product::factory()->create(['category_id' => $category1->id]);
    $product3 = Product::factory()->create(['category_id' => $category2->id]);

    $collection = new ProductCollection([$product1, $product2, $product3]);

    $result = $collection->byCategory($category1->id);

    expect($result)
        ->toBeInstanceOf(ProductCollection::class)
        ->toHaveCount(2)
        ->and($result->pluck('id')->toArray())
        ->toBe([$product1->id, $product2->id]);
});

test('by category returns empty collection when no products in category', function (): void {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    $product = Product::factory()->create(['category_id' => $category1->id]);

    $collection = new ProductCollection([$product]);

    expect($collection->byCategory($category2->id))->toHaveCount(0);
});

test('by category handles products without category', function (): void {
    $category = Category::factory()->create();

    $product1 = Product::factory()->create(['category_id' => $category->id]);
    $product2 = Product::factory()->create(['category_id' => null]);

    $collection = new ProductCollection([$product1, $product2]);

    $result = $collection->byCategory($category->id);

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($product1->id);
});

test('low stock returns products below alert threshold', function (): void {
    $store = Store::factory()->create();

    $lowStockProduct = Product::factory()->create(['alert_quantity' => 10]);
    Inventory::factory()->create([
        'product_id' => $lowStockProduct->id,
        'store_id' => $store->id,
        'total_quantity' => 5, // Below alert_quantity of 10
    ]);

    $normalStockProduct = Product::factory()->create(['alert_quantity' => 10]);
    Inventory::factory()->create([
        'product_id' => $normalStockProduct->id,
        'store_id' => $store->id,
        'total_quantity' => 15, // Above alert_quantity
    ]);

    $collection = new ProductCollection([$lowStockProduct, $normalStockProduct]);

    $result = $collection->lowStock($store->id);

    expect($result)
        ->toBeInstanceOf(ProductCollection::class)
        ->toHaveCount(1)
        ->and($result->first()->id)->toBe($lowStockProduct->id);
});

test('low stock returns products at exact alert threshold', function (): void {
    $store = Store::factory()->create();

    $atThresholdProduct = Product::factory()->create(['alert_quantity' => 10]);
    Inventory::factory()->create([
        'product_id' => $atThresholdProduct->id,
        'store_id' => $store->id,
        'total_quantity' => 10, // Exactly at alert_quantity
    ]);

    $collection = new ProductCollection([$atThresholdProduct]);

    $result = $collection->lowStock($store->id);

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($atThresholdProduct->id);
});

test('low stock returns empty collection when no products are low', function (): void {
    $store = Store::factory()->create();

    $product = Product::factory()->create(['alert_quantity' => 10]);
    Inventory::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'total_quantity' => 20,
    ]);

    $collection = new ProductCollection([$product]);

    expect($collection->lowStock($store->id))->toHaveCount(0);
});

test('low stock handles products with no inventory', function (): void {
    $store = Store::factory()->create();

    $productWithInventory = Product::factory()->create(['alert_quantity' => 10]);
    Inventory::factory()->create([
        'product_id' => $productWithInventory->id,
        'store_id' => $store->id,
        'total_quantity' => 5,
    ]);

    $productWithoutInventory = Product::factory()->create(['alert_quantity' => 10]);

    $collection = new ProductCollection([$productWithInventory, $productWithoutInventory]);

    $result = $collection->lowStock($store->id);

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($productWithInventory->id);
});

test('total selling value returns sum of selling prices', function (): void {
    $product1 = Product::factory()->create(['selling_price' => 1000]);
    $product2 = Product::factory()->create(['selling_price' => 2500]);
    $product3 = Product::factory()->create(['selling_price' => 1500]);

    $collection = new ProductCollection([$product1, $product2, $product3]);

    expect($collection->totalSellingValue())->toBe(5000);
});

test('total selling value returns zero for empty collection', function (): void {
    $collection = new ProductCollection([]);

    expect($collection->totalSellingValue())->toBe(0);
});

test('total selling value handles single product', function (): void {
    $product = Product::factory()->create(['selling_price' => 999]);

    $collection = new ProductCollection([$product]);

    expect($collection->totalSellingValue())->toBe(999);
});
