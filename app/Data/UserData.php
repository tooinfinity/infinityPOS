<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\AutoLazy;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

#[AutoLazy]
final class UserData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        /** @var Lazy|DataCollection<int|string, RoleData>|null */
        public Lazy|DataCollection|null $roles = null,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at = null,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at = null,
    ) {}
}
