<?php

declare(strict_types=1);

namespace App\Actions\ExpenseCategory;

use App\Exceptions\InvalidOperationException;
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
        /** @var bool $result */
        $result = DB::transaction(static function () use ($category): bool {
            throw_if($category->expenses()->exists(), InvalidOperationException::class, 'delete', 'ExpenseCategory', 'Cannot delete a category that has associated expenses.');

            return (bool) $category->delete();
        });

        return $result;
    }
}
