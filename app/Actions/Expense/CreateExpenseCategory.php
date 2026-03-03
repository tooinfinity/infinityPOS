<?php

declare(strict_types=1);

namespace App\Actions\Expense;

use App\Data\Expense\CreateExpenseCategoryData;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateExpenseCategory
{
    /**
     * @throws Throwable
     */
    public function handle(CreateExpenseCategoryData $data): ExpenseCategory
    {
        return DB::transaction(static fn (): ExpenseCategory => ExpenseCategory::query()->forceCreate([
            'name' => $data->name,
            'description' => $data->description,
            'is_active' => $data->is_active,
        ])->refresh());
    }
}
