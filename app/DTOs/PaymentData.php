<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\PaymentMethodEnum;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class PaymentData extends Data
{
    public function __construct(
        #[Required]
        #[MapInputName('payment_method')]
        public PaymentMethodEnum $paymentMethod,

        #[Required, Min(0)]
        public int $amount,

        #[Nullable, Max(100)]
        #[MapInputName('reference_number')]
        public ?string $referenceNumber = null,

        #[Nullable]
        #[MapInputName('payment_date')]
        public ?string $paymentDate = null,

        #[Nullable]
        public ?string $notes = null,
    ) {}
}
