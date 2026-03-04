<?php

declare(strict_types=1);

use App\Actions\Category\CreateCategory;
use App\Data\Category\CreateCategoryData;
use App\Models\Category;

it('may create a category', function (): void {
    $action = resolve(CreateCategory::class);

    $data = new CreateCategoryData(
        name: 'Test Category',
        slug: 'test-category',
        description: null,
        is_active: true,
    );

    $category = $action->handle($data);

    expect($category)->toBeInstanceOf(Category::class)
        ->and($category->name)->toBe('Test Category')
        ->and($category->slug)->toBe('test-category')
        ->and($category->exists)->toBeTrue();
});

it('creates category with description', function (): void {
    $action = resolve(CreateCategory::class);

    $data = new CreateCategoryData(
        name: 'Test Category',
        slug: 'test-category',
        description: 'Test description',
        is_active: true,
    );

    $category = $action->handle($data);

    expect($category->description)->toBe('Test description');
});

it('creates category with is_active flag', function (): void {
    $action = resolve(CreateCategory::class);

    $data = new CreateCategoryData(
        name: 'Test Category',
        slug: 'test-category',
        description: null,
        is_active: false,
    );

    $category = $action->handle($data);

    expect($category->is_active)->toBeFalse();
});
