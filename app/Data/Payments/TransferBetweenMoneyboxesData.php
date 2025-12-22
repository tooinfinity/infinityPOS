<?php

declare(strict_types=1);

namespace App\Data\Payments;

use Spatie\LaravelData\Data;

final class TransferBetweenMoneyboxesData extends Data
{
    public function __construct(
        public int $from_moneybox_id,
        public int $to_moneybox_id,
        public int $amount,
        public ?string $reference,
        public ?string $notes,
        public int $created_by,
    ) {}
}
