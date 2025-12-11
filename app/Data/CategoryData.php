<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\CategoryTypeEnum;
use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class CategoryData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $code,
        public CategoryTypeEnum $type,
        public bool $is_active,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}
}
