<?php

declare(strict_types=1);

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

test('to array', function (): void {
    $expense = Expense::factory()->create()->refresh();

    expect(array_keys($expense->toArray()))
        ->toBe([
            'id',
            'expense_category_id',
            'user_id',
            'reference_no',
            'amount',
            'expense_date',
            'description',
            'document',
            'created_at',
            'updated_at',
        ]);
});

dataset('expense_belongs_to_relationships', [
    'expenseCategory' => fn (): array => ['relation' => 'expenseCategory', 'model' => ExpenseCategory::class, 'foreignKey' => 'expense_category_id'],
    'user' => fn (): array => ['relation' => 'user', 'model' => User::class, 'foreignKey' => 'user_id'],
]);

it('belongs to {relation}', function (array $config): void {
    $expense = new Expense();

    expect($expense->{$config['relation']}())
        ->toBeInstanceOf(BelongsTo::class);
})->with('expense_belongs_to_relationships');

it('can access {relation}', function (array $config): void {
    $related = $config['model']::factory()->create();
    $expense = Expense::factory()->create([
        $config['foreignKey'] => $related->id,
    ]);

    expect($expense->{$config['relation']})
        ->toBeInstanceOf($config['model'])
        ->id->toBe($related->id);
})->with('expense_belongs_to_relationships');
