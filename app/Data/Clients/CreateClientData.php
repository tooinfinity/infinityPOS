<?php

declare(strict_types=1);

namespace App\Data\Clients;

use Spatie\LaravelData\Data;

final class CreateClientData extends Data
{
    public function __construct(
        public string $name,
        public ?string $phone,
        public ?string $email,
        public ?string $address,
        public ?string $article,
        public ?string $nif,
        public ?string $nis,
        public ?string $rc,
        public ?string $rib,
        public bool $is_active,
        public int $created_by,
    ) {}
}
