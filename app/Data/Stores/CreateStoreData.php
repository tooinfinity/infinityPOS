<?php

declare(strict_types=1);

namespace App\Data\Stores;

use Spatie\LaravelData\Data;

final class CreateStoreData extends Data
{
    public function __construct(
        public string $name,
        public ?string $city,
        public ?string $address,
        public ?string $phone,
        public bool $is_active,
        public int $created_by,
    ) {}
}
