<?php

declare(strict_types=1);

namespace App\Data\Payment;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdatePaymentMethodData extends Data
{
    public function __construct(
        public string|Optional $name,
        public string|Optional $code,
        public bool|Optional $is_active,
    ) {}
}
