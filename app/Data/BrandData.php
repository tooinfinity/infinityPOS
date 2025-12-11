<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Brand;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class BrandData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public bool $is_active,
        #[Lazy] public ?UserData $creator,
        #[Lazy] public ?UserData $updater,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}

    public static function fromModel(Brand $brand): self
    {
        return new self(
            id: $brand->id,
            name: $brand->name,
            is_active: $brand->is_active,
            creator: $brand->creator ? UserData::from($brand->creator) : null,
            updater: $brand->updater ? UserData::from($brand->updater) : null,
            created_at: $brand->created_at?->toDateTimeString(),
            updated_at: $brand->updated_at?->toDateTimeString(),
        );
    }
}
