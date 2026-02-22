<?php

declare(strict_types=1);

use App\Actions\Expense\CreateExpense;
use App\Data\Expense\CreateExpenseData;
use App\Models\Expense;
use App\Models\ExpenseCategory;

it('may create an expense', function (): void {
    $category = ExpenseCategory::factory()->create();

    $action = resolve(CreateExpense::class);

    $data = new CreateExpenseData(
        expense_category_id: $category->id,
        user_id: null,
        reference_no: null,
        amount: 1000,
        expense_date: now(),
        description: null,
        document: null,
    );

    $expense = $action->handle($data);

    expect($expense)->toBeInstanceOf(Expense::class)
        ->and($expense->expense_category_id)->toBe($category->id)
        ->and($expense->amount)->toBe(1000)
        ->and($expense->exists)->toBeTrue()
        ->and($expense->reference_no)->toStartWith('EXP-');
});

it('creates expense with custom reference number', function (): void {
    $category = ExpenseCategory::factory()->create();

    $action = resolve(CreateExpense::class);

    $data = new CreateExpenseData(
        expense_category_id: $category->id,
        user_id: null,
        reference_no: 'CUSTOM-001',
        amount: 1000,
        expense_date: now(),
        description: null,
        document: null,
    );

    $expense = $action->handle($data);

    expect($expense->reference_no)->toBe('CUSTOM-001');
});

it('creates expense with description', function (): void {
    $category = ExpenseCategory::factory()->create();

    $action = resolve(CreateExpense::class);

    $data = new CreateExpenseData(
        expense_category_id: $category->id,
        user_id: null,
        reference_no: null,
        amount: 1000,
        expense_date: now(),
        description: 'Test expense description',
        document: null,
    );

    $expense = $action->handle($data);

    expect($expense->description)->toBe('Test expense description');
});

it('creates expense with user', function (): void {
    $category = ExpenseCategory::factory()->create();
    $user = App\Models\User::factory()->create();

    $action = resolve(CreateExpense::class);

    $data = new CreateExpenseData(
        expense_category_id: $category->id,
        user_id: $user->id,
        reference_no: null,
        amount: 1000,
        expense_date: now(),
        description: null,
        document: null,
    );

    $expense = $action->handle($data);

    expect($expense->user_id)->toBe($user->id);
});
