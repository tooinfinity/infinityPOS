<?php

declare(strict_types=1);

use App\Actions\Expense\UpdateExpense;
use App\Data\Expense\ExpenseData;
use App\Models\Expense;
use App\Models\ExpenseCategory;

it('may update an expense', function (): void {
    $category = ExpenseCategory::factory()->create();
    $expense = Expense::factory()->for($category)->create([
        'amount' => 100,
        'description' => 'Old description',
    ]);

    $action = resolve(UpdateExpense::class);

    $data = new ExpenseData(
        expense_category_id: $category->id,
        amount: 200,
        expense_date: $expense->expense_date,
        description: 'New description',
    );

    $updatedExpense = $action->handle($expense, $data);

    expect($updatedExpense->amount)->toBe(200)
        ->and($updatedExpense->description)->toBe('New description');
});

it('updates only provided fields', function (): void {
    $category = ExpenseCategory::factory()->create();
    $newCategory = ExpenseCategory::factory()->create();
    $expense = Expense::factory()->for($category)->create([
        'amount' => 100,
        'description' => 'Old description',
    ]);

    $action = resolve(UpdateExpense::class);

    $data = new ExpenseData(
        expense_category_id: $newCategory->id,
        amount: 100,
        expense_date: $expense->expense_date,
        description: null,
    );

    $updatedExpense = $action->handle($expense, $data);

    expect($updatedExpense->expense_category_id)->toBe($newCategory->id)
        ->and($updatedExpense->amount)->toBe(100)
        ->and($updatedExpense->description)->toBe('Old description');
});
