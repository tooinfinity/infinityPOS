<?php

declare(strict_types=1);

use App\Models\Product;

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
