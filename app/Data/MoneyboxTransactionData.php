<?php

declare(strict_types=1);

namespace App\Data;

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
}
