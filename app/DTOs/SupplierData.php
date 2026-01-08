<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class SupplierData extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $name,

        #[Max(255)]
        #[MapInputName('contact_person')]
        public ?string $contactPerson = null,

        #[Max(20)]
        #[MapInputName('phone')]
        public ?string $phone = null,

        #[Email, Max(255)]
        #[MapInputName('email')]
        public ?string $email = null,

        #[MapInputName('address')]
        public ?string $address = null,
    ) {}
}
