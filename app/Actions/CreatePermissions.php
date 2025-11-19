<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PermissionEnum;
use Spatie\Permission\Models\Permission;

final readonly class CreatePermissions
{
    /**
     * @return array<string, Permission>
     */
    public function handle(string $guardName = 'web'): array
    {
        /** @var array<string, Permission> $createdPermissions */
        $createdPermissions = [];

        foreach (PermissionEnum::cases() as $permission) {
            $createdPermissions[$permission->value] = Permission::query()->firstOrCreate(
                ['name' => $permission->value],
                ['guard_name' => $guardName]
            );
        }

        return $createdPermissions;
    }
}
