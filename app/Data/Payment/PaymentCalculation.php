<?php

declare(strict_types=1);

namespace App\Data\Payment;

use App\Enums\PaymentStatusEnum;
use Spatie\LaravelData\Data;

final class PaymentCalculation extends Data
{
    public function __construct(
        public PaymentStatusEnum $paymentStatus,
        public int $changeAmount,
        public int $dueAmount,
    ) {}
}
