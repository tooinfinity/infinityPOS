<?php

declare(strict_types=1);

use App\Actions\Expenses\DeleteExpense;
use App\Models\Expense;
use App\Models\User;

it('may delete an expense', function (): void {
    $user = User::factory()->create();
    $expense = Expense::factory()->create(['created_by' => $user->id]);

    $action = resolve(DeleteExpense::class);
    $action->handle($expense);

    expect(Expense::query()->find($expense->id))->toBeNull()
        ->and($expense->created_by)->toBeNull();
});
