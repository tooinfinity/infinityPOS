<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Expense;
use App\Models\Product;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();

    $category = Category::factory()->create(['created_by' => $user->id])->refresh();

    expect(array_keys($category->toArray()))
        ->toBe([
            'id',
            'name',
            'code',
            'type',
            'is_active',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});

test('category relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $category = Category::factory()->create(['created_by' => $user->id]);
    $expense = Expense::factory()->create(['category_id' => $category->id, 'created_by' => $user->id]);
    $product = Product::factory()->create(['category_id' => $category->id, 'created_by' => $user->id]);

    $category->update(['updated_by' => $user->id]);

    expect($category->expenses)->toHaveCount(1)
        ->and($category->products)->toHaveCount(1)
        ->and($category->expenses->first()->id)->toBe($expense->id)
        ->and($category->products->first()->id)->toBe($product->id)
        ->and($category->creator->id)->toBe($user->id)
        ->and($category->updater->id)->toBe($user->id);
});
