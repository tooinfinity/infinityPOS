<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Moneybox;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class MoneyboxData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $type,
        public ?string $description,
        public int $balance,
        public ?string $bank_name,
        public ?string $account_number,
        public bool $is_active,
        #[Lazy] public ?StoreData $store,
        #[Lazy] public ?UserData $creator,
        #[Lazy] public ?UserData $updater,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}

    public static function fromModel(Moneybox $moneybox): self
    {
        return new self(
            id: $moneybox->id,
            name: $moneybox->name,
            type: $moneybox->type,
            description: $moneybox->description,
            balance: $moneybox->balance,
            bank_name: $moneybox->bank_name,
            account_number: $moneybox->account_number,
            is_active: $moneybox->is_active,
            store: $moneybox->store ? StoreData::from($moneybox->store) : null,
            creator: $moneybox->creator ? UserData::from($moneybox->creator) : null,
            updater: $moneybox->updater ? UserData::from($moneybox->updater) : null,
            created_at: $moneybox->created_at?->toDayDateTimeString(),
            updated_at: $moneybox->updated_at?->toDayDateTimeString(),
        );
    }
}
