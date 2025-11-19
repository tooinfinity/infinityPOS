<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\RoleEnum;
use App\Models\User;
use Spatie\Permission\Models\Role;

final readonly class AssignRoleToUser
{
    public function handle(User $user, RoleEnum $role): User
    {
        $roleModel = Role::query()
            ->where('name', $role->value)
            ->firstOrFail();

        $user->assignRole($roleModel);

        return $user;
    }
}
