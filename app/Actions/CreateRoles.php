<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\RoleEnum;
use Spatie\Permission\Models\Role;

final readonly class CreateRoles
{
    /**
     * @return array<string, Role>
     */
    public function handle(string $guardName = 'web'): array
    {
        $createdRoles = [];

        foreach (RoleEnum::cases() as $role) {
            $createdRoles[$role->value] = Role::query()->firstOrCreate(
                ['name' => $role->value],
                ['guard_name' => $guardName]
            );
        }

        return $createdRoles;
    }
}
