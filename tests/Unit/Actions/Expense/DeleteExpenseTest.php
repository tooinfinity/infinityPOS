<?php

declare(strict_types=1);

use App\Actions\Expense\DeleteExpense;
use App\Models\Expense;
use App\Models\ExpenseCategory;

it('may delete an expense', function (): void {
    $category = ExpenseCategory::factory()->create();
    $expense = Expense::factory()->forCategory($category)->create();

    $action = resolve(DeleteExpense::class);

    $result = $action->handle($expense);

    expect($result)->toBeTrue()
        ->and($expense->exists)->toBeFalse();
});

it('deletes expense without affecting category', function (): void {
    $category = ExpenseCategory::factory()->create();
    $expense = Expense::factory()->forCategory($category)->create();

    $action = resolve(DeleteExpense::class);
    $action->handle($expense);

    expect(ExpenseCategory::query()->find($category->id))->not->toBeNull();
});
