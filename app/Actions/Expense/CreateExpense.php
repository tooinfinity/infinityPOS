<?php

declare(strict_types=1);

namespace App\Actions\Expense;

use App\Actions\GenerateReferenceNo;
use App\Data\Expense\ExpenseData;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateExpense
{
    public function __construct(
        private GenerateReferenceNo $referenceGenerator,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(ExpenseData $data): Expense
    {
        /** @var Expense $expense */
        $expense = DB::transaction(function () use ($data): Expense {
            return Expense::query()->forceCreate([
                'expense_category_id' => $data->expense_category_id,
                'user_id' => auth()->id(),
                'reference_no' => $this->referenceGenerator->handle('EXP', Expense::class),
                'amount' => $data->amount,
                'expense_date' => $data->expense_date,
                'description' => $data->description,
            ])->refresh();
        });

        return $expense;
    }
}
