<?php

declare(strict_types=1);

use App\Models\Expense;

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
