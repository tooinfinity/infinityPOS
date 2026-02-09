<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;

test('to array', function (): void {
    $product = Product::factory()->create()->refresh();

    expect(array_keys($product->toArray()))
        ->toBe([
            'id',
            'category_id',
            'brand_id',
            'unit_id',
            'name',
            'sku',
            'barcode',
            'description',
            'image',
            'cost_price',
            'selling_price',
            'quantity',
            'alert_quantity',
            'track_inventory',
            'is_active',
            'created_at',
            'updated_at',
        ]);
});

test('only returns active products by default', function (): void {
    Product::factory()->count(2)->create([
        'is_active' => true,
    ]);
    Product::factory()->count(2)->create([
        'is_active' => false,
    ]);

    $products = Product::all();

    expect($products)
        ->toHaveCount(2);
});

it('belongs to a category', function (): void {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    expect($product->category)
        ->toBeInstanceOf(Category::class)
        ->id->toBe($category->id);
});

it('belongs to a brand', function (): void {
    $brand = Brand::factory()->create();
    $product = Product::factory()->create(['brand_id' => $brand->id]);

    expect($product->brand)
        ->toBeInstanceOf(Brand::class)
        ->id->toBe($brand->id);
});

it('belongs to a unit', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->create(['unit_id' => $unit->id]);

    expect($product->unit)
        ->toBeInstanceOf(Unit::class)
        ->id->toBe($unit->id);
});
