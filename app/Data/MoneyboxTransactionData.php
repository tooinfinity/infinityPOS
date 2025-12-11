<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\MoneyboxTransaction;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class MoneyboxTransactionData extends Data
{
    public function __construct(
        public int $id,
        public string $type,
        public int $amount,
        public int $balance_after,
        public ?string $reference,
        public ?string $notes,
        #[Lazy] public ?MoneyboxData $moneybox,
        #[Lazy] public ?PaymentData $payment,
        #[Lazy] public ?ExpenseData $expense,
        #[Lazy] public ?MoneyboxData $transferTo,
        #[Lazy] public ?UserData $creator,
        #[Lazy] public ?UserData $updater,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}

    public static function fromModel(MoneyboxTransaction $transaction): self
    {
        return new self(
            id: $transaction->id,
            type: $transaction->type,
            amount: $transaction->amount,
            balance_after: $transaction->balance_after,
            reference: $transaction->reference,
            notes: $transaction->notes,
            moneybox: $transaction->moneybox ? MoneyboxData::from($transaction->moneybox) : null,
            payment: $transaction->payment ? PaymentData::from($transaction->payment) : null,
            expense: $transaction->expense ? ExpenseData::from($transaction->expense) : null,
            transferTo: $transaction->transferTo ? MoneyboxData::from($transaction->transferTo) : null,
            creator: $transaction->creator ? UserData::from($transaction->creator) : null,
            updater: $transaction->updater ? UserData::from($transaction->updater) : null,
            created_at: $transaction->created_at?->toDayDateTimeString(),
            updated_at: $transaction->updated_at?->toDayDateTimeString(),
        );
    }
}
