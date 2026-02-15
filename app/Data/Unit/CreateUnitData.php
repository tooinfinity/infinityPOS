<?php

declare(strict_types=1);

namespace App\Data\Unit;

use Spatie\LaravelData\Data;

final class CreateUnitData extends Data
{
    public function __construct(
        public string $name,
        public string $short_name,
        public bool $is_active,
    ) {}
}
