<?php

declare(strict_types=1);

namespace App\Actions\Expense;

use App\Data\Expense\CreateExpenseData;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final readonly class CreateExpense
{
    /**
     * @throws Throwable
     */
    public function handle(CreateExpenseData $data): Expense
    {
        return DB::transaction(function () use ($data): Expense {
            $referenceNo = $data->reference_no ?? $this->generateReferenceNo();

            return Expense::query()->forceCreate([
                'expense_category_id' => $data->expense_category_id,
                'user_id' => $data->user_id,
                'reference_no' => $referenceNo,
                'amount' => $data->amount,
                'expense_date' => $data->expense_date,
                'description' => $data->description,
                'document' => $data->document,
            ])->refresh();
        });
    }

    private function generateReferenceNo(): string
    {
        do {
            $reference = 'EXP-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (Expense::query()->where('reference_no', $reference)->exists());

        return $reference;
    }
}
