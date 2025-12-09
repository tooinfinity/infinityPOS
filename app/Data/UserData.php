<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

final class UserData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
        /** @var Lazy|DataCollection<int|string, RoleData> */
        public Lazy|DataCollection $roles,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            created_at: $user->created_at,
            updated_at: $user->updated_at,
            roles: Lazy::whenLoaded('roles', $user,
                /**
                 * @return Collection<int|string, RoleData>
                 */
                fn (): Collection => RoleData::collect($user->roles)
            ),
        );
    }
}
