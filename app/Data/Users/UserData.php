<?php

declare(strict_types=1);

namespace App\Data\Users;

use App\Models\User;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

final class UserData extends Data
{
    /**
     * @param  array<int, string>  $roles
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        #[WithCast(DateTimeInterfaceCast::class)]
        public string $created_at,
        /** @var array<int, string> */
        public array $roles,
    ) {}

    public static function fromModel(User $user): self
    {
        /** @var array<int, string> $roles */
        $roles = $user->relationLoaded('roles')
            ? $user->roles->pluck('name')->toArray()
            : $user->getRoleNames()->toArray();

        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            created_at: $user->created_at->toDateTimeString(),
            roles: $roles,
        );
    }
}
