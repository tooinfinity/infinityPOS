<?php

declare(strict_types=1);

namespace App\Data;

use App\Data\Expenses\ExpenseData;
use App\Data\Users\UserData;
use App\Enums\MoneyboxTransactionTypeEnum;
use Spatie\LaravelData\Attributes\AutoLazy;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

#[AutoLazy]
final class MoneyboxTransactionData extends Data
{
    public function __construct(
        public int $id,
        public MoneyboxTransactionTypeEnum $type,
        public int $amount,
        public int $balance_after,
        public ?string $reference,
        public ?string $notes,
        public Lazy|MoneyboxData|null $moneybox,
        public Lazy|PaymentData|null $payment,
        public Lazy|ExpenseData|null $expense,
        public Lazy|UserData|null $creator,
        public Lazy|UserData|null $updater,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}
}
