<?php

declare(strict_types=1);

namespace App\Data\PurchaseReturn;

use DateTimeInterface;
use Spatie\LaravelData\Data;

final class RefundPurchaseReturnData extends Data
{
    public function __construct(
        public int $payment_method_id,
        public int $amount,
        public DateTimeInterface|string $payment_date,
        public ?int $user_id = null,
        public ?string $note = null,
    ) {}
}
