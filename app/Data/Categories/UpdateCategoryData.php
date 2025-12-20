<?php

declare(strict_types=1);

namespace App\Data\Categories;

use App\Enums\CategoryTypeEnum;
use Spatie\LaravelData\Data;

final class UpdateCategoryData extends Data
{
    public function __construct(
        public ?string $name,
        public ?string $code,
        public ?CategoryTypeEnum $type,
        public ?bool $is_active,
        public int $updated_by,
    ) {}
}
