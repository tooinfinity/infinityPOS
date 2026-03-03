<?php

declare(strict_types=1);

namespace App\Data\Warehouse;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateWarehouseData extends Data
{
    public function __construct(
        public string|Optional $name,
        public string|Optional $code,
        public string|Optional|null $email,
        public string|Optional|null $phone,
        public string|Optional|null $address,
        public string|Optional|null $city,
        public string|Optional|null $country,
        public bool|Optional $is_active,
    ) {}
}
