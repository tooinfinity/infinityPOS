<?php

declare(strict_types=1);

namespace App\Data\SaleReturn;

use DateTimeInterface;
use Spatie\LaravelData\Data;

final class RefundSaleReturnData extends Data
{
    public function __construct(
        public int $payment_method_id,
        public int $amount,
        public DateTimeInterface|string $payment_date,
        public ?int $user_id = null,
        public ?string $note = null,
    ) {}
}
