<?php

declare(strict_types=1);

use App\Models\Expense;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();

    $expense = Expense::factory()->create(['created_by' => $user->id])->refresh();

    expect(array_keys($expense->toArray()))
        ->toBe([
            'id',
            'amount',
            'description',
            'category_id',
            'store_id',
            'moneybox_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});
