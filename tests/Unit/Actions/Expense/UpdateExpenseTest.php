<?php

declare(strict_types=1);

use App\Actions\Expense\UpdateExpense;
use App\Data\Expense\UpdateExpenseData;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Spatie\LaravelData\Optional;

it('may update an expense amount', function (): void {
    $category = ExpenseCategory::factory()->create();
    $expense = Expense::factory()->forCategory($category)->create([
        'amount' => 1000,
    ]);

    $action = resolve(UpdateExpense::class);

    $data = new UpdateExpenseData(
        expense_category_id: Optional::create(),
        user_id: Optional::create(),
        reference_no: Optional::create(),
        amount: 2000,
        expense_date: Optional::create(),
        description: Optional::create(),
        document: Optional::create(),
    );

    $updatedExpense = $action->handle($expense, $data);

    expect($updatedExpense->amount)->toBe(2000);
});

it('updates expense category', function (): void {
    $category = ExpenseCategory::factory()->create();
    $newCategory = ExpenseCategory::factory()->create();
    $expense = Expense::factory()->forCategory($category)->create();

    $action = resolve(UpdateExpense::class);

    $data = new UpdateExpenseData(
        expense_category_id: $newCategory->id,
        user_id: Optional::create(),
        reference_no: Optional::create(),
        amount: Optional::create(),
        expense_date: Optional::create(),
        description: Optional::create(),
        document: Optional::create(),
    );

    $updatedExpense = $action->handle($expense, $data);

    expect($updatedExpense->expense_category_id)->toBe($newCategory->id);
});

it('updates description', function (): void {
    $category = ExpenseCategory::factory()->create();
    $expense = Expense::factory()->forCategory($category)->create([
        'description' => 'Old description',
    ]);

    $action = resolve(UpdateExpense::class);

    $data = new UpdateExpenseData(
        expense_category_id: Optional::create(),
        user_id: Optional::create(),
        reference_no: Optional::create(),
        amount: Optional::create(),
        expense_date: Optional::create(),
        description: 'New description',
        document: Optional::create(),
    );

    $updatedExpense = $action->handle($expense, $data);

    expect($updatedExpense->description)->toBe('New description');
});

it('updates expense date', function (): void {
    $category = ExpenseCategory::factory()->create();
    $expense = Expense::factory()->forCategory($category)->create([
        'expense_date' => now()->subDay(),
    ]);

    $action = resolve(UpdateExpense::class);

    $newDate = now();

    $data = new UpdateExpenseData(
        expense_category_id: Optional::create(),
        user_id: Optional::create(),
        reference_no: Optional::create(),
        amount: Optional::create(),
        expense_date: $newDate,
        description: Optional::create(),
        document: Optional::create(),
    );

    $updatedExpense = $action->handle($expense, $data);

    expect($updatedExpense->expense_date->toDateString())->toBe($newDate->toDateString());
});

it('updates user_id', function (): void {
    $category = ExpenseCategory::factory()->create();
    $user = App\Models\User::factory()->create();
    $expense = Expense::factory()->forCategory($category)->create([
        'user_id' => null,
    ]);

    $action = resolve(UpdateExpense::class);

    $data = new UpdateExpenseData(
        expense_category_id: Optional::create(),
        user_id: $user->id,
        reference_no: Optional::create(),
        amount: Optional::create(),
        expense_date: Optional::create(),
        description: Optional::create(),
        document: Optional::create(),
    );

    $updatedExpense = $action->handle($expense, $data);

    expect($updatedExpense->user_id)->toBe($user->id);
});

it('updates reference_no', function (): void {
    $category = ExpenseCategory::factory()->create();
    $expense = Expense::factory()->forCategory($category)->create([
        'reference_no' => 'OLD-001',
    ]);

    $action = resolve(UpdateExpense::class);

    $data = new UpdateExpenseData(
        expense_category_id: Optional::create(),
        user_id: Optional::create(),
        reference_no: 'NEW-001',
        amount: Optional::create(),
        expense_date: Optional::create(),
        description: Optional::create(),
        document: Optional::create(),
    );

    $updatedExpense = $action->handle($expense, $data);

    expect($updatedExpense->reference_no)->toBe('NEW-001');
});

it('updates document', function (): void {
    $category = ExpenseCategory::factory()->create();
    $expense = Expense::factory()->forCategory($category)->create([
        'document' => null,
    ]);

    $action = resolve(UpdateExpense::class);

    $data = new UpdateExpenseData(
        expense_category_id: Optional::create(),
        user_id: Optional::create(),
        reference_no: Optional::create(),
        amount: Optional::create(),
        expense_date: Optional::create(),
        description: Optional::create(),
        document: '/documents/expense.pdf',
    );

    $updatedExpense = $action->handle($expense, $data);

    expect($updatedExpense->document)->toBe('/documents/expense.pdf');
});
