<?php

declare(strict_types=1);

use App\Actions\Category\UpdateCategory;
use App\Data\Category\UpdateCategoryData;
use App\Models\Category;
use Spatie\LaravelData\Optional;

it('may update a category name', function (): void {
    $category = Category::factory()->create([
        'name' => 'Old Name',
        'slug' => 'old-name',
    ]);

    $action = resolve(UpdateCategory::class);

    $data = new UpdateCategoryData(
        name: 'New Name',
        slug: 'new-name',
        description: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedCategory = $action->handle($category, $data);

    expect($updatedCategory->name)->toBe('New Name')
        ->and($updatedCategory->slug)->toBe('new-name');
});

it('updates description', function (): void {
    $category = Category::factory()->create([
        'description' => 'Old description',
    ]);

    $action = resolve(UpdateCategory::class);

    $data = new UpdateCategoryData(
        name: Optional::create(),
        slug: Optional::create(),
        description: 'New description',
        is_active: Optional::create(),
    );

    $updatedCategory = $action->handle($category, $data);

    expect($updatedCategory->description)->toBe('New description');
});

it('updates is_active status', function (): void {
    $category = Category::factory()->create([
        'is_active' => true,
    ]);

    $action = resolve(UpdateCategory::class);

    $data = new UpdateCategoryData(
        name: Optional::create(),
        slug: Optional::create(),
        description: Optional::create(),
        is_active: false,
    );

    $updatedCategory = $action->handle($category, $data);

    expect($updatedCategory->is_active)->toBeFalse();
});
