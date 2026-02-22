<?php

declare(strict_types=1);

namespace App\Data\Expense;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateExpenseData extends Data
{
    public function __construct(
        public int|Optional $expense_category_id,
        public int|Optional|null $user_id,
        public string|Optional $reference_no,
        public int|Optional $amount,
        public CarbonInterface|Optional $expense_date,
        public string|Optional|null $description,
        public string|Optional|null $document,
    ) {}
}
