<?php

declare(strict_types=1);

namespace App\Data\Customer;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateCustomerData extends Data
{
    public function __construct(
        public string|Optional $name,
        public string|null|Optional $email,
        public string|null|Optional $phone,
        public string|null|Optional $address,
        public string|null|Optional $city,
        public string|null|Optional $country,
        public bool|Optional $is_active,
    ) {}
}
