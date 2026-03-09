<?php

declare(strict_types=1);

namespace App\Actions\ExpenseCategory;

use App\Data\ExpenseCategory\ExpenseCategoryData;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateExpenseCategory
{
    /**
     * @throws Throwable
     */
    public function handle(ExpenseCategoryData $data): ExpenseCategory
    {
        /** @var ExpenseCategory $category */
        $category = DB::transaction(
            static fn (): ExpenseCategory => ExpenseCategory::query()->forceCreate([
                'name' => $data->name,
                'description' => $data->description,
                'is_active' => $data->is_active,
            ])->refresh()
        );

        return $category;
    }
}
