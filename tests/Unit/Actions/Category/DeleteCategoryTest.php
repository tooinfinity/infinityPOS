<?php

declare(strict_types=1);

use App\Actions\Category\DeleteCategory;
use App\Models\Category;
use App\Models\Product;

it('may delete a category', function (): void {
    $category = Category::factory()->create();

    $action = resolve(DeleteCategory::class);

    $result = $action->handle($category);

    expect($result)->toBeTrue()
        ->and($category->exists)->toBeFalse();
});

it('nullifies category_id on associated products when deleting', function (): void {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
    ]);

    expect($product->category_id)->toBe($category->id);

    $action = resolve(DeleteCategory::class);
    $action->handle($category);

    expect($product->refresh()->category_id)->toBeNull();
});

it('nullifies category_id on multiple associated products when deleting', function (): void {
    $category = Category::factory()->create();
    $products = Product::factory()->count(3)->create([
        'category_id' => $category->id,
    ]);

    $action = resolve(DeleteCategory::class);
    $action->handle($category);

    foreach ($products as $product) {
        expect($product->refresh()->category_id)->toBeNull();
    }
});

it('deletes category without products', function (): void {
    $category = Category::factory()->create();

    $action = resolve(DeleteCategory::class);

    $result = $action->handle($category);

    expect($result)->toBeTrue()
        ->and(Category::query()->find($category->id))->toBeNull();
});
