<?php

declare(strict_types=1);

use App\Actions\ExpenseCategory\UpdateExpenseCategory;
use App\Data\ExpenseCategory\ExpenseCategoryData;
use App\Models\ExpenseCategory;

it('may update an expense category', function (): void {
    $category = ExpenseCategory::factory()->create([
        'name' => 'Old Name',
        'description' => 'Old description',
        'is_active' => true,
    ]);

    $action = resolve(UpdateExpenseCategory::class);

    $data = new ExpenseCategoryData(
        name: 'New Name',
        description: 'New description',
        is_active: false,
    );

    $updatedCategory = $action->handle($category, $data);

    expect($updatedCategory->name)->toBe('New Name')
        ->and($updatedCategory->description)->toBe('New description')
        ->and($updatedCategory->is_active)->toBeFalse();
});

it('updates only provided fields', function (): void {
    $category = ExpenseCategory::factory()->create([
        'name' => 'Original Name',
        'description' => 'Original description',
        'is_active' => true,
    ]);

    $action = resolve(UpdateExpenseCategory::class);

    $data = new ExpenseCategoryData(
        name: 'Updated Name',
        description: null,
        is_active: true,
    );

    $updatedCategory = $action->handle($category, $data);

    expect($updatedCategory->name)->toBe('Updated Name')
        ->and($updatedCategory->description)->toBe('Original description')
        ->and($updatedCategory->is_active)->toBeTrue();
});
