<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Category;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class CategoryData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $code,
        public string $type,
        public bool $is_active,
        #[Lazy] public ?UserData $creator,
        #[Lazy] public ?UserData $updater,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}

    public static function fromModel(Category $category): self
    {
        return new self(
            id: $category->id,
            name: $category->name,
            code: $category->code,
            type: $category->type,
            is_active: $category->is_active,
            creator: $category->creator ? UserData::from($category->creator) : null,
            updater: $category->updater ? UserData::from($category->updater) : null,
            created_at: $category->created_at?->toDayDateTimeString(),
            updated_at: $category->updated_at?->toDayDateTimeString(),
        );
    }
}
