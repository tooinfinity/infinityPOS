<?php

declare(strict_types=1);

namespace App\Data\Expense;

use Spatie\LaravelData\Data;

final class CreateExpenseCategoryData extends Data
{
    public function __construct(
        public string $name,
        public ?string $description,
        public bool $is_active,
    ) {}
}
