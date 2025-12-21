<?php

declare(strict_types=1);

namespace App\Data\Sales;

use App\Enums\PaymentMethodEnum;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class ProcessSalePaymentData extends Data
{
    public function __construct(
        #[Required, Min(1)]
        public int $amount,
        #[Required]
        public PaymentMethodEnum $method,
        public ?string $reference,
        public ?string $notes,
    ) {}
}
