<?php

declare(strict_types=1);

namespace App\Data\Expense;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;

final class CreateExpenseData extends Data
{
    public function __construct(
        public int $expense_category_id,
        public ?int $user_id,
        public ?string $reference_no,
        public int $amount,
        public CarbonInterface $expense_date,
        public ?string $description,
        public ?string $document,
    ) {}
}
