<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\AutoLazy;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

#[AutoLazy]
final class ExpenseData extends Data
{
    public function __construct(
        public int $id,
        public int $amount,
        public ?string $description,
        public Lazy|CategoryData|null $category,
        public Lazy|StoreData|null $store,
        public Lazy|MoneyboxData|null $moneybox,
        public Lazy|UserData|null $creator,
        public Lazy|UserData|null $updater,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}
}
