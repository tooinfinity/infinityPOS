<?php

declare(strict_types=1);

use App\Actions\ExpenseCategory\CreateExpenseCategory;
use App\Data\ExpenseCategory\ExpenseCategoryData;
use App\Models\ExpenseCategory;

it('may create an expense category', function (): void {
    $action = resolve(CreateExpenseCategory::class);

    $data = new ExpenseCategoryData(
        name: 'Test Category',
        description: 'Test description',
        is_active: true,
    );

    $category = $action->handle($data);

    expect($category)->toBeInstanceOf(ExpenseCategory::class)
        ->and($category->exists)->toBeTrue()
        ->and($category->name)->toBe('Test Category')
        ->and($category->description)->toBe('Test description')
        ->and($category->is_active)->toBeTrue();
});

it('creates inactive category', function (): void {
    $action = resolve(CreateExpenseCategory::class);

    $data = new ExpenseCategoryData(
        name: 'Inactive Category',
        description: null,
        is_active: false,
    );

    $category = $action->handle($data);

    expect($category->is_active)->toBeFalse();
});

it('creates category with null description', function (): void {
    $action = resolve(CreateExpenseCategory::class);

    $data = new ExpenseCategoryData(
        name: 'No Description Category',
        description: null,
        is_active: true,
    );

    $category = $action->handle($data);

    expect($category->description)->toBeNull();
});
