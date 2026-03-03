<?php

declare(strict_types=1);

use App\Actions\Category\CreateCategory;
use App\Data\Category\CreateCategoryData;
use App\Models\Category;
use Illuminate\Support\Str;

it('may create a category', function (): void {
    $action = resolve(CreateCategory::class);

    $data = new CreateCategoryData(
        name: 'Test Category',
        slug: null,
        description: null,
        is_active: true,
    );

    $category = $action->handle($data);

    expect($category)->toBeInstanceOf(Category::class)
        ->and($category->name)->toBe('Test Category')
        ->and($category->slug)->toBe('test-category')
        ->and($category->exists)->toBeTrue();
});

it('creates category with custom slug', function (): void {
    $action = resolve(CreateCategory::class);

    $data = new CreateCategoryData(
        name: 'Test Category',
        slug: 'custom-slug',
        description: null,
        is_active: true,
    );

    $category = $action->handle($data);

    expect($category->slug)->toBe('custom-slug');
});

it('generates unique slug when duplicate exists', function (): void {
    Category::factory()->create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    $action = resolve(CreateCategory::class);

    $data = new CreateCategoryData(
        name: 'Test Category',
        slug: null,
        description: null,
        is_active: true,
    );

    $category = $action->handle($data);

    expect($category->slug)->toBe('test-category-1');
});

it('creates category with description', function (): void {
    $action = resolve(CreateCategory::class);

    $data = new CreateCategoryData(
        name: 'Test Category',
        slug: null,
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
        slug: null,
        description: null,
        is_active: false,
    );

    $category = $action->handle($data);

    expect($category->is_active)->toBeFalse();
});

it('generates slug from name when not provided', function (): void {
    $action = resolve(CreateCategory::class);

    $data = new CreateCategoryData(
        name: 'My Special Category',
        slug: null,
        description: null,
        is_active: true,
    );

    $category = $action->handle($data);

    expect($category->slug)->toBe(Str::slug('My Special Category'));
});
