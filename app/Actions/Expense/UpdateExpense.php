<?php

declare(strict_types=1);

namespace App\Actions\Expense;

use App\Data\Expense\ExpenseData;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdateExpense
{
    /**
     * @throws Throwable
     */
    public function handle(Expense $expense, ExpenseData $data): Expense
    {
        /** @var Expense $result */
        $result = DB::transaction(static function () use ($expense, $data): Expense {
            $updatedData = [
                'expense_category_id' => $data->expense_category_id ?? $expense->expense_category_id,
                'amount' => $data->amount ?? $expense->amount,
                'expense_date' => $data->expense_date ?? $expense->expense_date,
                'description' => $data->description ?? $expense->description,
            ];

            $expense->update($updatedData);

            return $expense->refresh();
        });

        return $result;
    }
}
