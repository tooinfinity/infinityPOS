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
    public function __construct(private GenerateReferenceNo $generateReferenceNo) {}

    /**
     * @throws Throwable
     */
    public function handle(CreateExpenseData $data): Expense
    {
        return DB::transaction(fn (): Expense => Expense::query()->forceCreate([
            'expense_category_id' => $data->expense_category_id,
            'user_id' => $data->user_id,
            'reference_no' => $this->generateReferenceNo->handle('EXP', Expense::class),
            'amount' => $data->amount,
            'expense_date' => $data->expense_date,
            'description' => $data->description,
            'document' => $data->document,
        ])->refresh());
    }
}
