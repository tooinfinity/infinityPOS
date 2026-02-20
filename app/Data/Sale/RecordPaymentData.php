<?php

declare(strict_types=1);

namespace App\Data\Sale;

use DateTimeInterface;
use Spatie\LaravelData\Data;

final class RecordPaymentData extends Data
{
    public function __construct(
        public int $payment_method_id,
        public int $amount,
        public DateTimeInterface|string $payment_date,
        public ?int $user_id,
        public ?string $note,
    ) {}
}
