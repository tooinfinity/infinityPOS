<?php

declare(strict_types=1);

namespace App\Actions\Expenses;

use App\Data\Expenses\CreateExpenseData;
use App\Models\Expense;

final readonly class CreateExpense
{
    public function handle(CreateExpenseData $data): Expense
    {
        return Expense::query()->create([
            'amount' => $data->amount,
            'description' => $data->description,
            'category_id' => $data->category_id,
            'store_id' => $data->store_id,
            'moneybox_id' => $data->moneybox_id,
            'created_by' => $data->created_by,
        ]);
    }
}
