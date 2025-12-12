<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\SettingTypeEnum;
use Spatie\LaravelData\Attributes\AutoLazy;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

#[AutoLazy]
final class SettingData extends Data
{
    public function __construct(
        public int $id,
        public string $key,
        public ?string $value,
        public SettingTypeEnum $type,
        public ?string $group,
        public ?string $description,
        public Lazy|UserData|null $updater,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}
}
