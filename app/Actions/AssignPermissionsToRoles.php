<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\RoleEnum;
use Spatie\Permission\Models\Role;

final readonly class AssignPermissionsToRoles
{
    /**
     * @param  array<string, Role>  $roles
     * @return array<string, array{role: Role, permissions_count: int}>
     */
    public function handle(array $roles): array
    {
        /** @var array<string, array{role: Role, permissions_count: int}> $result */
        $result = [];

        foreach (RoleEnum::cases() as $roleEnum) {
            if (! isset($roles[$roleEnum->value])) {
                continue;
            }

            $role = $roles[$roleEnum->value];

            /** @var array<int, string> $permissions */
            $permissions = collect($roleEnum->permissions())
                ->map(fn ($permission): string => $permission->value)
                ->all();

            $role->syncPermissions($permissions);

            $result[$roleEnum->value] = [
                'role' => $role,
                'permissions_count' => count($permissions),
            ];
        }

        return $result;
    }
}
