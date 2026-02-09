<?php

declare(strict_types=1);

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('to array', function (): void {
    $expenseCategory = ExpenseCategory::factory()->create()->refresh();

    expect(array_keys($expenseCategory->toArray()))
        ->toBe([
            'id',
            'name',
            'description',
            'is_active',
            'created_at',
            'updated_at',
        ]);
});

test('only returns active expense categories by default', function (): void {
    ExpenseCategory::factory()->count(2)->create([
        'is_active' => true,
    ]);
    ExpenseCategory::factory()->count(2)->create([
        'is_active' => false,
    ]);

    $expenseCategories = ExpenseCategory::all();

    expect($expenseCategories)
        ->toHaveCount(2);
});

it('has many expenses', function (): void {
    $expenseCategory = new ExpenseCategory();

    expect($expenseCategory->expenses())
        ->toBeInstanceOf(HasMany::class);
});

it('can create expenses', function (): void {
    $expenseCategory = ExpenseCategory::factory()->create();
    Expense::factory()->count(3)->create(['expense_category_id' => $expenseCategory->id]);

    expect($expenseCategory->expenses)
        ->toHaveCount(3)
        ->each->toBeInstanceOf(Expense::class);
});

it('returns empty collection when no expenses exist', function (): void {
    $expenseCategory = ExpenseCategory::factory()->create();

    expect($expenseCategory->expenses)
        ->toBeEmpty()
        ->toBeInstanceOf(Collection::class);
});
