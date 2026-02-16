<?php

declare(strict_types=1);

namespace App\Data\Customer;

use Spatie\LaravelData\Data;

final class CreateCustomerData extends Data
{
    public function __construct(
        public string $name,
        public ?string $email,
        public ?string $phone,
        public ?string $address,
        public ?string $city,
        public ?string $country,
        public bool $is_active,
    ) {}
}
