<?php

declare(strict_types=1);

namespace App\Data\Category;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateCategoryData extends Data
{
    public function __construct(
        public string|Optional $name,
        public string|Optional|null $description,
        public bool|Optional $is_active,
    ) {}
}
