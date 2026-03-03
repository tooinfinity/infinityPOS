<?php

declare(strict_types=1);

namespace App\Actions\Expense;

use App\Data\Expense\UpdateExpenseCategoryData;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;
use Throwable;

final readonly class UpdateExpenseCategory
{
    /**
     * @throws Throwable
     */
    public function handle(ExpenseCategory $category, UpdateExpenseCategoryData $data): ExpenseCategory
    {
        return DB::transaction(static function () use ($category, $data): ExpenseCategory {
            $updateData = [];

            if (! $data->name instanceof Optional) {
                $updateData['name'] = $data->name;
            }

            if (! $data->description instanceof Optional) {
                $updateData['description'] = $data->description;
            }

            if (! $data->is_active instanceof Optional) {
                $updateData['is_active'] = $data->is_active;
            }

            $category->update($updateData);

            return $category->refresh();
        });
    }
}
