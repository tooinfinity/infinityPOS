<?php

declare(strict_types=1);

use App\Models\Category;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('to array', function (): void {
    $category = Category::factory()->create()->refresh();

    expect(array_keys($category->toArray()))
        ->toBe([
            'id',
            'name',
            'description',
            'created_at',
            'updated_at',
        ]);
});

test('products relationship returns has many', function (): void {
    $category = new Category();

    expect($category->products())
        ->toBeInstanceOf(HasMany::class);
});

test('casts returns correct array', function (): void {
    $category = new Category();

    expect($category->casts())
        ->toBe([
            'id' => 'integer',
            'name' => 'string',
            'description' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $category = Category::factory()->create()->refresh();

    expect($category->id)->toBeInt()
        ->and($category->name)->toBeString()
        ->and($category->created_at)->toBeInstanceOf(DateTimeInterface::class)
        ->and($category->updated_at)->toBeInstanceOf(DateTimeInterface::class);
});
