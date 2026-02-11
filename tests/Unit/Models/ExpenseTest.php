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

it('filters by recent scope', function (): void {
    Expense::factory()->create(['expense_date' => now()->subDays(10)]);
    Expense::factory()->create(['expense_date' => now()->subDays(35)]);
    Expense::factory()->create(['expense_date' => now()->subDays(5)]);

    $results = Expense::recent()->get();

    expect($results)->toHaveCount(2);
});

it('filters by recent scope with custom days', function (): void {
    Expense::factory()->create(['expense_date' => now()->subDays(10)]);
    Expense::factory()->create(['expense_date' => now()->subDays(20)]);

    $results = Expense::recent(15)->get();

    expect($results)->toHaveCount(1);
});

it('filters by today scope', function (): void {
    Expense::factory()->create(['expense_date' => now()]);
    Expense::factory()->create(['expense_date' => now()->subDay()]);
    Expense::factory()->create(['expense_date' => now()->addDay()]);

    $results = Expense::today()->get();

    expect($results)->toHaveCount(1);
});
