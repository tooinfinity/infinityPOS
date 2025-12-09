<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Expense;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
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
        /** @var Lazy|DataCollection<int|string, MoneyboxTransactionData> */
        public Lazy|DataCollection $moneyboxTransactions,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}

    public static function fromModel(Expense $expense): self
    {
        return new self(
            id: $expense->id,
            amount: $expense->amount,
            description: $expense->description,
            category: Lazy::whenLoaded('category', $expense, fn (): ?CategoryData => $expense->category ? CategoryData::from($expense->category) : null
            ),
            store: Lazy::whenLoaded('store', $expense, fn (): ?StoreData => $expense->store ? StoreData::from($expense->store) : null
            ),
            moneybox: Lazy::whenLoaded('moneybox', $expense, fn (): ?MoneyboxData => $expense->moneybox ? MoneyboxData::from($expense->moneybox) : null
            ),
            moneyboxTransactions: Lazy::whenLoaded('moneyboxTransactions', $expense,
                /**
                 * @return Collection<int|string, MoneyboxTransactionData>
                 */
                fn (): Collection => MoneyboxTransactionData::collect($expense->moneyboxTransactions)
            ),
            creator: Lazy::whenLoaded('creator', $expense, fn (): UserData => UserData::from($expense->creator)
            ),
            updater: Lazy::whenLoaded('updater', $expense, fn (): ?UserData => $expense->updater ? UserData::from($expense->updater) : null
            ),
            created_at: $expense->created_at,
            updated_at: $expense->updated_at,
        );
    }
}
