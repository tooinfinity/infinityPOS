<?php

declare(strict_types=1);

use App\Actions\Expense\DeleteExpense;
use App\Models\Expense;

describe(DeleteExpense::class, function (): void {
    it('may delete an expense', function (): void {
        $expense = Expense::factory()->create();

        $action = resolve(DeleteExpense::class);

        $result = $action->handle($expense);

        expect($result)->toBeTrue()
            ->and(Expense::query()->where('id', $expense->id)->exists())->toBeFalse();
    });
});
