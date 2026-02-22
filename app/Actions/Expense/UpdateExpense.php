<?php

declare(strict_types=1);

namespace App\Actions\Expense;

use App\Data\Expense\UpdateExpenseData;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;
use Throwable;

final readonly class UpdateExpense
{
    /**
     * @throws Throwable
     */
    public function handle(Expense $expense, UpdateExpenseData $data): Expense
    {
        return DB::transaction(static function () use ($expense, $data): Expense {
            $updateData = [];

            if (! $data->expense_category_id instanceof Optional) {
                $updateData['expense_category_id'] = $data->expense_category_id;
            }

            if (! $data->user_id instanceof Optional) {
                $updateData['user_id'] = $data->user_id;
            }

            if (! $data->reference_no instanceof Optional) {
                $updateData['reference_no'] = $data->reference_no;
            }

            if (! $data->amount instanceof Optional) {
                $updateData['amount'] = $data->amount;
            }

            if (! $data->expense_date instanceof Optional) {
                $updateData['expense_date'] = $data->expense_date;
            }

            if (! $data->description instanceof Optional) {
                $updateData['description'] = $data->description;
            }

            if (! $data->document instanceof Optional) {
                $updateData['document'] = $data->document;
            }

            $expense->update($updateData);

            return $expense->refresh();
        });
    }
}
