<?php

declare(strict_types=1);

use App\Actions\Category\UpdateCategoryAction;
use App\Models\Category;

it('may update a category name', function (): void {
    $category = Category::factory()->create([
        'name' => 'Old Name',
        'slug' => 'old-name',
    ]);

    $action = resolve(UpdateCategoryAction::class);

    $updatedCategory = $action->handle($category, [
        'name' => 'New Name',
    ]);

    expect($updatedCategory->name)->toBe('New Name')
        ->and($updatedCategory->slug)->toBe('new-name');
});

it('updates slug when name changes and no slug provided', function (): void {
    $category = Category::factory()->create([
        'name' => 'Old Name',
        'slug' => 'old-name',
    ]);

    $action = resolve(UpdateCategoryAction::class);

    $updatedCategory = $action->handle($category, [
        'name' => 'New Name',
    ]);

    expect($updatedCategory->slug)->toBe('new-name');
});

it('keeps existing slug when name changes but slug is provided', function (): void {
    $category = Category::factory()->create([
        'name' => 'Old Name',
        'slug' => 'custom-slug',
    ]);

    $action = resolve(UpdateCategoryAction::class);

    $updatedCategory = $action->handle($category, [
        'name' => 'New Name',
        'slug' => 'custom-slug',
    ]);

    expect($updatedCategory->slug)->toBe('custom-slug');
});

it('generates unique slug when updating to existing slug', function (): void {
    Category::factory()->create([
        'name' => 'Existing Category',
        'slug' => 'existing-slug',
    ]);

    $category = Category::factory()->create([
        'name' => 'Another Category',
        'slug' => 'another-slug',
    ]);

    $action = resolve(UpdateCategoryAction::class);

    $updatedCategory = $action->handle($category, [
        'slug' => 'existing-slug',
    ]);

    expect($updatedCategory->slug)->toBe('existing-slug-1');
});

it('allows keeping own slug unchanged', function (): void {
    $category = Category::factory()->create([
        'name' => 'Test Category',
        'slug' => 'test-slug',
    ]);

    $action = resolve(UpdateCategoryAction::class);

    $updatedCategory = $action->handle($category, [
        'name' => 'Updated Category',
        'slug' => 'test-slug',
    ]);

    expect($updatedCategory->slug)->toBe('test-slug');
});

it('updates description', function (): void {
    $category = Category::factory()->create([
        'description' => 'Old description',
    ]);

    $action = resolve(UpdateCategoryAction::class);

    $updatedCategory = $action->handle($category, [
        'description' => 'New description',
    ]);

    expect($updatedCategory->description)->toBe('New description');
});

it('updates is_active status', function (): void {
    $category = Category::factory()->create([
        'is_active' => true,
    ]);

    $action = resolve(UpdateCategoryAction::class);

    $updatedCategory = $action->handle($category, [
        'is_active' => false,
    ]);

    expect($updatedCategory->is_active)->toBeFalse();
});
