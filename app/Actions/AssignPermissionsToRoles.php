<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Throwable;

final readonly class AssignPermissionsToRoles
{
    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        DB::transaction(function (): void {
            foreach (RoleEnum::cases() as $roleEnum) {
                $role = Role::query()->firstOrCreate([
                    'name' => $roleEnum->value,
                    'guard_name' => 'web',
                ]);

                $permissions = PermissionEnum::forRole($roleEnum);

                $role->syncPermissions($permissions);
            }
        });
    }
}
