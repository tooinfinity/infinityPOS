<?php

declare(strict_types=1);

namespace App\Actions\Expense;

use App\Actions\GenerateReferenceNo;
use App\Data\Expense\CreateExpenseData;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateExpense
{
    /**
     * @throws Throwable
     */
    public function handle(CreateExpenseData $data): Expense
    {
        return DB::transaction(static fn (): Expense => Expense::query()->forceCreate([
            'expense_category_id' => $data->expense_category_id,
            'user_id' => $data->user_id,
            'reference_no' => new GenerateReferenceNo('EXP', Expense::query())->handle(),
            'amount' => $data->amount,
            'expense_date' => $data->expense_date,
            'description' => $data->description,
            'document' => $data->document,
        ])->refresh());
    }
}
