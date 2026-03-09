<?php

declare(strict_types=1);

namespace App\Actions\ExpenseCategory;

use App\Data\ExpenseCategory\ExpenseCategoryData;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdateExpenseCategory
{
    /**
     * @throws Throwable
     */
    public function handle(ExpenseCategory $category, ExpenseCategoryData $data): ExpenseCategory
    {
        /** @var ExpenseCategory $result */
        $result = DB::transaction(static function () use ($category, $data): ExpenseCategory {
            $updatedData = [
                'name' => $data->name ?? $category->name,
                'description' => $data->description ?? $category->description,
                'is_active' => $data->is_active ?? $category->is_active,
            ];

            $category->update($updatedData);

            return $category->refresh();
        });

        return $result;
    }
}
