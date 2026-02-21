<?php

declare(strict_types=1);

namespace App\Data\Payment;

use Spatie\LaravelData\Data;

final class CreatePaymentMethodData extends Data
{
    public function __construct(
        public string $name,
        public string $code,
        public bool $is_active,
    ) {}
}
