<?php

declare(strict_types=1);

use App\Actions\Category\CreateCategoryAction;
use App\Models\Category;
use Illuminate\Support\Str;

it('may create a category', function (): void {
    $action = resolve(CreateCategoryAction::class);

    $category = $action->handle([
        'name' => 'Test Category',
    ]);

    expect($category)->toBeInstanceOf(Category::class)
        ->and($category->name)->toBe('Test Category')
        ->and($category->slug)->toBe('test-category')
        ->and($category->exists)->toBeTrue();
});

it('creates category with custom slug', function (): void {
    $action = resolve(CreateCategoryAction::class);

    $category = $action->handle([
        'name' => 'Test Category',
        'slug' => 'custom-slug',
    ]);

    expect($category->slug)->toBe('custom-slug');
});

it('generates unique slug when duplicate exists', function (): void {
    Category::factory()->create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    $action = resolve(CreateCategoryAction::class);

    $category = $action->handle([
        'name' => 'Test Category',
    ]);

    expect($category->slug)->toBe('test-category-1');
});

it('creates category with description', function (): void {
    $action = resolve(CreateCategoryAction::class);

    $category = $action->handle([
        'name' => 'Test Category',
        'description' => 'Test description',
    ]);

    expect($category->description)->toBe('Test description');
});

it('creates category with is_active flag', function (): void {
    $action = resolve(CreateCategoryAction::class);

    $category = $action->handle([
        'name' => 'Test Category',
        'is_active' => false,
    ]);

    expect($category->is_active)->toBeFalse();
});

it('generates slug from name when not provided', function (): void {
    $action = resolve(CreateCategoryAction::class);

    $category = $action->handle([
        'name' => 'My Special Category',
    ]);

    expect($category->slug)->toBe(Str::slug('My Special Category'));
});
