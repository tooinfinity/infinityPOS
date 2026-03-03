<?php

declare(strict_types=1);

use App\Actions\Expense\DeleteExpenseCategory;
use App\Models\Expense;
use App\Models\ExpenseCategory;

it('may delete an expense category', function (): void {
    $category = ExpenseCategory::factory()->create();

    $action = resolve(DeleteExpenseCategory::class);

    $result = $action->handle($category);

    expect($result)->toBeTrue()
        ->and($category->exists)->toBeFalse();
});

it('deletes associated expenses when deleting category', function (): void {
    $category = ExpenseCategory::factory()->create();
    $expense = Expense::factory()->forCategory($category)->create();

    expect(Expense::query()->count())->toBe(1);

    $action = resolve(DeleteExpenseCategory::class);
    $action->handle($category);

    expect(Expense::query()->count())->toBe(0);
});

it('deletes multiple associated expenses when deleting category', function (): void {
    $category = ExpenseCategory::factory()->create();
    $expenses = Expense::factory()->count(3)->forCategory($category)->create();

    expect(Expense::query()->count())->toBe(3);

    $action = resolve(DeleteExpenseCategory::class);
    $action->handle($category);

    expect(Expense::query()->count())->toBe(0);
});

it('deletes category without expenses', function (): void {
    $category = ExpenseCategory::factory()->create();

    $action = resolve(DeleteExpenseCategory::class);

    $result = $action->handle($category);

    expect($result)->toBeTrue()
        ->and(ExpenseCategory::query()->find($category->id))->toBeNull();
});
