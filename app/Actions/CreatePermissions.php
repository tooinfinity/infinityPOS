<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PermissionEnum;
use Spatie\Permission\Models\Permission;

final readonly class CreatePermissions
{
    /**
     * @return array<int, Permission>
     */
    public function handle(string $guardName = 'web'): array
    {
        $createdPermissions = [];

        foreach (PermissionEnum::cases() as $permission) {
            $createdPermissions[] = Permission::query()->firstOrCreate(
                ['name' => $permission->value],
                ['guard_name' => $guardName]
            );
        }

        return $createdPermissions;
    }

    public function count(): int
    {
        return count(PermissionEnum::cases());
    }
}
