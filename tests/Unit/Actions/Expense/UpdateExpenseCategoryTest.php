<?php

declare(strict_types=1);

use App\Actions\Expense\UpdateExpenseCategory;
use App\Data\Expense\UpdateExpenseCategoryData;
use App\Models\ExpenseCategory;
use Spatie\LaravelData\Optional;

it('may update an expense category name', function (): void {
    $category = ExpenseCategory::factory()->create([
        'name' => 'Old Name',
    ]);

    $action = resolve(UpdateExpenseCategory::class);

    $data = new UpdateExpenseCategoryData(
        name: 'New Name',
        description: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedCategory = $action->handle($category, $data);

    expect($updatedCategory->name)->toBe('New Name');
});

it('updates description', function (): void {
    $category = ExpenseCategory::factory()->create([
        'description' => 'Old description',
    ]);

    $action = resolve(UpdateExpenseCategory::class);

    $data = new UpdateExpenseCategoryData(
        name: Optional::create(),
        description: 'New description',
        is_active: Optional::create(),
    );

    $updatedCategory = $action->handle($category, $data);

    expect($updatedCategory->description)->toBe('New description');
});

it('updates is_active status', function (): void {
    $category = ExpenseCategory::factory()->create([
        'is_active' => true,
    ]);

    $action = resolve(UpdateExpenseCategory::class);

    $data = new UpdateExpenseCategoryData(
        name: Optional::create(),
        description: Optional::create(),
        is_active: false,
    );

    $updatedCategory = $action->handle($category, $data);

    expect($updatedCategory->is_active)->toBeFalse();
});
