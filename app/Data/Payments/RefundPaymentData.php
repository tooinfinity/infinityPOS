<?php

declare(strict_types=1);

namespace App\Data\Payments;

use Spatie\LaravelData\Data;

final class RefundPaymentData extends Data
{
    public function __construct(
        public int $original_payment_id,
        public int $amount,
        public ?int $moneybox_id,
        public ?string $reason,
        public ?string $notes,
        public int $created_by,
    ) {}
}
