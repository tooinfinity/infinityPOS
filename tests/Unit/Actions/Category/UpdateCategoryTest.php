<?php

declare(strict_types=1);

use App\Actions\Category\UpdateCategory;
use App\Data\Category\CategoryData;
use App\Models\Category;

it('may update a category name', function (): void {
    $category = Category::factory()->create([
        'name' => 'Old Name',
    ]);

    $action = resolve(UpdateCategory::class);

    $data = new CategoryData(
        name: 'New Name',
        description: $category->description,
        is_active: $category->is_active,
    );

    $updatedCategory = $action->handle($category, $data);

    expect($updatedCategory->name)->toBe('New Name');
});

it('updates description', function (): void {
    $category = Category::factory()->create([
        'description' => 'Old description',
    ]);

    $action = resolve(UpdateCategory::class);

    $data = new CategoryData(
        name: $category->name,
        description: 'New description',
        is_active: $category->is_active,
    );

    $updatedCategory = $action->handle($category, $data);

    expect($updatedCategory->description)->toBe('New description');
});

it('updates is_active status', function (): void {
    $category = Category::factory()->create([
        'is_active' => true,
    ]);

    $action = resolve(UpdateCategory::class);

    $data = new CategoryData(
        name: $category->name,
        description: $category->description,
        is_active: false,
    );

    $updatedCategory = $action->handle($category, $data);

    expect($updatedCategory->is_active)->toBeFalse();
});
