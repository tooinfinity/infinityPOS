<?php

declare(strict_types=1);

namespace App\Actions\Expenses;

use App\Models\Expense;

final readonly class DeleteExpense
{
    public function handle(Expense $expense): void
    {
        $expense->update([
            'created_by' => null,
        ]);
        $expense->delete();
    }
}
