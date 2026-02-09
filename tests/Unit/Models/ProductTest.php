<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
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

dataset('product relationships', [
    'category' => fn (): array => ['relation' => 'category', 'model' => Category::class],
    'brand' => fn (): array => ['relation' => 'brand', 'model' => Brand::class],
]);

it('belongs to {relation}', function (array $config): void {
    $related = $config['model']::factory()->create();
    $product = Product::factory()->create([
        $config['relation'].'_id' => $related->id,
    ]);

    expect($product->{$config['relation']})
        ->toBeInstanceOf($config['model'])
        ->id->toBe($related->id);
})->with('product relationships');
