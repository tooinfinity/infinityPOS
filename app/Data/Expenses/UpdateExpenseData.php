<?php

declare(strict_types=1);

namespace App\Data\Expenses;

use Spatie\LaravelData\Data;

final class UpdateExpenseData extends Data
{
    public function __construct(
        public ?int $amount,
        public ?string $description,
        public ?int $category_id,
        public ?int $store_id,
        public ?int $moneybox_id,
        public int $updated_by,
    ) {}
}
