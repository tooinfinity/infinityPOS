<?php

declare(strict_types=1);

namespace App\Data\Payments;

use App\Enums\MoneyboxTransactionTypeEnum;
use Spatie\LaravelData\Data;

final class RecordMoneyboxTransactionData extends Data
{
    public function __construct(
        public int $moneybox_id,
        public MoneyboxTransactionTypeEnum $type,
        public int $amount,
        public ?string $reference,
        public ?string $notes,
        public ?int $payment_id,
        public ?int $expense_id,
        public ?int $transfer_to_moneybox_id,
        public int $created_by,
    ) {}
}
