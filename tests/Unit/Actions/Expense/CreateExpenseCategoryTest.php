<?php

declare(strict_types=1);

use App\Actions\Expense\CreateExpenseCategory;
use App\Data\Expense\CreateExpenseCategoryData;
use App\Models\ExpenseCategory;

it('may create an expense category', function (): void {
    $action = resolve(CreateExpenseCategory::class);

    $data = new CreateExpenseCategoryData(
        name: 'Test Category',
        description: null,
        is_active: true,
    );

    $category = $action->handle($data);

    expect($category)->toBeInstanceOf(ExpenseCategory::class)
        ->and($category->name)->toBe('Test Category')
        ->and($category->exists)->toBeTrue();
});

it('creates expense category with description', function (): void {
    $action = resolve(CreateExpenseCategory::class);

    $data = new CreateExpenseCategoryData(
        name: 'Test Category',
        description: 'Test description',
        is_active: true,
    );

    $category = $action->handle($data);

    expect($category->description)->toBe('Test description');
});

it('creates expense category with is_active flag', function (): void {
    $action = resolve(CreateExpenseCategory::class);

    $data = new CreateExpenseCategoryData(
        name: 'Test Category',
        description: null,
        is_active: false,
    );

    $category = $action->handle($data);

    expect($category->is_active)->toBeFalse();
});
