<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\MoneyboxTransaction;
use Carbon\CarbonInterface;
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
        public Lazy|MoneyboxData $moneybox,
        public Lazy|PaymentData|null $payment,
        public Lazy|ExpenseData|null $expense,
        public Lazy|MoneyboxData|null $transferTo,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
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
            moneybox: Lazy::whenLoaded('moneybox', $transaction, fn (): MoneyboxData => MoneyboxData::from($transaction->moneybox)
            ),
            payment: Lazy::whenLoaded('payment', $transaction, fn (): ?PaymentData => $transaction->payment ? PaymentData::from($transaction->payment) : null
            ),
            expense: Lazy::whenLoaded('expense', $transaction, fn (): ?ExpenseData => $transaction->expense ? ExpenseData::from($transaction->expense) : null
            ),
            transferTo: Lazy::whenLoaded('transferTo', $transaction, fn (): ?MoneyboxData => $transaction->transferTo ? MoneyboxData::from($transaction->transferTo) : null
            ),
            creator: Lazy::whenLoaded('creator', $transaction, fn (): UserData => UserData::from($transaction->creator)
            ),
            updater: Lazy::whenLoaded('updater', $transaction, fn (): ?UserData => $transaction->updater ? UserData::from($transaction->updater) : null
            ),
            created_at: $transaction->created_at,
            updated_at: $transaction->updated_at,
        );
    }
}
