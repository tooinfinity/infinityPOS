<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class PermissionData extends Data
{
    public function __construct(
        public readonly string $name,
    ) {}
}
