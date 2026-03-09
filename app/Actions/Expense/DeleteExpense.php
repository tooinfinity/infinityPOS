<?php

declare(strict_types=1);

namespace App\Actions\Expense;

use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DeleteExpense
{
    /**
     * @throws Throwable
     */
    public function handle(Expense $expense): bool
    {
        /** @var bool $result */
        $result = DB::transaction(
            static fn (): bool => (bool) $expense->delete()
        );

        return $result;
    }
}
