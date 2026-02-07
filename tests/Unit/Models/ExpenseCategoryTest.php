<?php

declare(strict_types=1);

use App\Models\ExpenseCategory;

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
