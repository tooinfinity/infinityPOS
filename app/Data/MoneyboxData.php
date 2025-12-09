<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Moneybox;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
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
        public Lazy|StoreData|null $store,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        /** @var Lazy|DataCollection<int|string, MoneyboxTransactionData> */
        public Lazy|DataCollection $transactions,
        /** @var Lazy|DataCollection<int|string, PaymentData> */
        public Lazy|DataCollection $payments,
        /** @var Lazy|DataCollection<int|string, ExpenseData> */
        public Lazy|DataCollection $expenses,
        /** @var Lazy|DataCollection<int|string, MoneyboxTransactionData> */
        public Lazy|DataCollection $incomingTransfers,
        /** @var Lazy|DataCollection<int|string, MoneyboxTransactionData> */
        public Lazy|DataCollection $outgoingTransfers,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
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
            store: Lazy::whenLoaded('store', $moneybox, fn (): ?StoreData => $moneybox->store ? StoreData::from($moneybox->store) : null
            ),
            creator: Lazy::whenLoaded('creator', $moneybox, fn (): UserData => UserData::from($moneybox->creator)
            ),
            updater: Lazy::whenLoaded('updater', $moneybox, fn (): ?UserData => $moneybox->updater ? UserData::from($moneybox->updater) : null
            ),
            transactions: Lazy::whenLoaded('transactions', $moneybox,
                /**
                 * @return Collection<int|string, MoneyboxTransactionData>
                 */
                fn (): Collection => MoneyboxTransactionData::collect($moneybox->transactions)),
            payments: Lazy::whenLoaded('payments', $moneybox,
                /**
                 * @return Collection<int|string, PaymentData>
                 */
                fn (): Collection => PaymentData::collect($moneybox->payments)),
            expenses: Lazy::whenLoaded('expenses', $moneybox,
                /**
                 * @return Collection<int|string, ExpenseData>
                 */
                fn (): Collection => ExpenseData::collect($moneybox->expenses)),
            incomingTransfers: Lazy::whenLoaded('incomingTransfers', $moneybox,
                /**
                 * @retrun Collection<int|string, MoneyboxTransactionData>
                 */
                fn (): Collection => MoneyboxTransactionData::collect($moneybox->incomingTransfers)),
            outgoingTransfers: Lazy::whenLoaded('outgoingTransfers', $moneybox,
                /**
                 * @return Collection<int|string, MoneyboxTransactionData>
                 */
                fn (): Collection => MoneyboxTransactionData::collect($moneybox->outgoingTransfers)),
            created_at: $moneybox->created_at,
            updated_at: $moneybox->updated_at,
        );
    }
}
