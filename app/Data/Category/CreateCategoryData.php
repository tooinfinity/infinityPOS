<?php

declare(strict_types=1);

namespace App\Data\Category;

use Spatie\LaravelData\Data;

final class CreateCategoryData extends Data
{
    public function __construct(
        public string $name,
        public ?string $slug,
        public ?string $description,
        public bool $is_active,
    ) {}
}
