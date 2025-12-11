<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Expense;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class ExpenseData extends Data
{
    public function __construct(
        public int $id,
        public int $amount,
        public ?string $description,
        #[Lazy] public ?CategoryData $category,
        #[Lazy] public ?StoreData $store,
        #[Lazy] public ?MoneyboxData $moneybox,
        #[Lazy] public ?UserData $creator,
        #[Lazy] public ?UserData $updater,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}

    public static function fromModel(Expense $expense): self
    {
        return new self(
            id: $expense->id,
            amount: $expense->amount,
            description: $expense->description,
            category: $expense->category ? CategoryData::from($expense->category) : null,
            store: $expense->store ? StoreData::from($expense->store) : null,
            moneybox: $expense->moneybox ? MoneyboxData::from($expense->moneybox) : null,
            creator: $expense->creator ? UserData::from($expense->creator) : null,
            updater: $expense->updater ? UserData::from($expense->updater) : null,
            created_at: $expense->created_at?->toDayDateTimeString(),
            updated_at: $expense->updated_at?->toDayDateTimeString(),
        );
    }
}
