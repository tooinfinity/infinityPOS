<?php

declare(strict_types=1);

namespace App\Data\Units;

use Spatie\LaravelData\Data;

final class UpdateUnitData extends Data
{
    public function __construct(
        public ?string $name,
        public ?string $short_name,
        public ?bool $is_active,
        public int $updated_by,
    ) {}
}
