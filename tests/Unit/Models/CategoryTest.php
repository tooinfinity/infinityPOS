<?php

declare(strict_types=1);

use App\Models\Category;

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
