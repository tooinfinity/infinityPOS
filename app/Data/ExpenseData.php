<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class ExpenseData extends Data
{
    public function __construct(
        public int $id,
        public int $amount,
        public ?string $description,
        public Lazy|CategoryData|null $category,
        public Lazy|StoreData|null $store,
        public Lazy|MoneyboxData|null $moneybox,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}
}
