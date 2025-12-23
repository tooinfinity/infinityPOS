<?php

declare(strict_types=1);

namespace App\Data\Pos;

use App\Enums\PaymentMethodEnum;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class ProcessPosPaymentData extends Data
{
    public function __construct(
        #[Required]
        public int $store_id,

        #[Required, Min(1)]
        public int $amount,

        #[Required]
        public PaymentMethodEnum $method,

        public ?int $client_id,
        public ?string $reference,
        public ?string $notes,
    ) {}
}
