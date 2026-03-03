<?php

declare(strict_types=1);

namespace App\Data\Payment;

use Spatie\LaravelData\Data;

final class VoidPaymentData extends Data
{
    public function __construct(
        public string $void_reason,
    ) {}
}
