<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;

test('to array', function (): void {
    $category = Category::factory()->create()->refresh();

    expect(array_keys($category->toArray()))
        ->toBe([
            'id',
            'name',
            'slug',
            'description',
            'is_active',
            'created_at',
            'updated_at',
        ]);
});

test('only returns active categories by default', function (): void {
    Category::factory()->count(2)->create([
        'is_active' => true,
    ]);
    Category::factory()->count(2)->create([
        'is_active' => false,
    ]);

    $categories = Category::all();

    expect($categories)
        ->toHaveCount(2);
});

test('category has many products', function (): void {
    $category = Category::factory()->create()->refresh();
    Product::factory()->create([
        'category_id' => $category->id,
    ]);

    expect($category->products)->toHaveCount(1);
});
