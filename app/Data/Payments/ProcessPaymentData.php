<?php

declare(strict_types=1);

namespace App\Data\Payments;

use App\Enums\PaymentMethodEnum;
use Spatie\LaravelData\Data;

final class ProcessPaymentData extends Data
{
    public function __construct(
        public int $amount,
        public PaymentMethodEnum $method,
        public ?int $moneybox_id,
        public ?string $reference,
        public ?string $notes,
        public ?string $related_type,
        public ?int $related_id,
        public int $created_by,
    ) {}
}
