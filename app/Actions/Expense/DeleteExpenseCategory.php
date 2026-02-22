<?php

declare(strict_types=1);

namespace App\Actions\Expense;

use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DeleteExpenseCategory
{
    /**
     * @throws Throwable
     */
    public function handle(ExpenseCategory $category): bool
    {
        return DB::transaction(static function () use ($category): bool {
            $category->expenses()->delete();

            return (bool) $category->delete();
        });
    }
}
