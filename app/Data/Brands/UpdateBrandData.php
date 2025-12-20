<?php

declare(strict_types=1);

namespace App\Data\Brands;

use Spatie\LaravelData\Data;

final class UpdateBrandData extends Data
{
    public function __construct(
        public ?string $name,
        public ?bool $is_active,
        public int $updated_by
    ) {}
}
