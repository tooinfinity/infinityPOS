<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;

final class UserData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}
}
