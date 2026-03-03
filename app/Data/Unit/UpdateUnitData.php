<?php

declare(strict_types=1);

namespace App\Data\Unit;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateUnitData extends Data
{
    public function __construct(
        public string|Optional $name,
        public string|Optional $short_name,
        public bool|Optional $is_active,
    ) {}
}
