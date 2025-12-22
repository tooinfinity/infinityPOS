<?php

declare(strict_types=1);

namespace App\Actions\Expenses;

use App\Data\Expenses\UpdateExpenseData;
use App\Models\Expense;

final readonly class UpdateExpense
{
    public function handle(Expense $expense, UpdateExpenseData $data): void
    {
        $updateData = array_filter([
            'amount' => $data->amount,
            'description' => $data->description,
            'category_id' => $data->category_id,
            'store_id' => $data->store_id,
            'moneybox_id' => $data->moneybox_id,
        ], static fn (mixed $value): bool => $value !== null);

        $updateData['updated_by'] = $data->updated_by;

        $expense->update($updateData);
    }
}
