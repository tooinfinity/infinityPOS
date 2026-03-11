<?php

declare(strict_types=1);

use App\Actions\Expense\CreateExpense;
use App\Data\Expense\ExpenseData;
use App\Models\Expense;
use App\Models\ExpenseCategory;

it('may create an expense', function (): void {
    $category = ExpenseCategory::factory()->create();

    $action = resolve(CreateExpense::class);

    $data = new ExpenseData(
        expense_category_id: $category->id,
        amount: 1000,
        expense_date: now(),
        description: 'Test expense',
    );

    $expense = $action->handle($data);

    expect($expense)->toBeInstanceOf(Expense::class)
        ->and($expense->exists)->toBeTrue()
        ->and($expense->expense_category_id)->toBe($category->id)
        ->and($expense->amount)->toBe(1000)
        ->and($expense->description)->toBe('Test expense')
        ->and($expense->reference_no)->not->toBeNull();
});

it('generates reference number on create', function (): void {
    $category = ExpenseCategory::factory()->create();

    $action = resolve(CreateExpense::class);

    $data = new ExpenseData(
        expense_category_id: $category->id,
        amount: 500,
        expense_date: now(),
        description: null,
    );

    $expense = $action->handle($data);

    expect($expense->reference_no)->toBeString()
        ->toStartWith('EXP');
});

it('creates expense with null description', function (): void {
    $category = ExpenseCategory::factory()->create();

    $action = resolve(CreateExpense::class);

    $data = new ExpenseData(
        expense_category_id: $category->id,
        amount: 250,
        expense_date: now(),
        description: null,
    );

    $expense = $action->handle($data);

    expect($expense->description)->toBeNull();
});
