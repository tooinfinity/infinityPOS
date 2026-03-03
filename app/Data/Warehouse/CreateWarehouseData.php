<?php

declare(strict_types=1);

namespace App\Data\Warehouse;

use Spatie\LaravelData\Data;

final class CreateWarehouseData extends Data
{
    public function __construct(
        public string $name,
        public string $code,
        public ?string $email,
        public ?string $phone,
        public ?string $address,
        public ?string $city,
        public ?string $country,
        public bool $is_active,
    ) {}
}
