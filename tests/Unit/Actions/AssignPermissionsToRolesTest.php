<?php

declare(strict_types=1);

use App\Actions\AssignPermissionsToRoles;
use App\Actions\SyncPermissions;
use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use Spatie\Permission\Models\Role;

it('assigns permissions to all roles', function (): void {

    foreach (RoleEnum::cases() as $roleEnum) {
        Role::create(['name' => $roleEnum->value]);
    }

    (new SyncPermissions)->handle();

    $action = new AssignPermissionsToRoles;
    $action->handle();

    foreach (RoleEnum::cases() as $roleEnum) {
        $role = Role::findByName($roleEnum->value);
        $expectedPermissions = PermissionEnum::forRole($roleEnum);

        expect($role)->not->toBeNull()
            ->and($role->permissions()->count())->toBe(count($expectedPermissions));

        $rolePermissionNames = $role->permissions()->pluck('name')->toArray();

        foreach ($expectedPermissions as $permission) {
            expect($rolePermissionNames)->toContain($permission);
        }
    }

});

it('runs within a database transaction', function (): void {
    foreach (RoleEnum::cases() as $roleEnum) {
        Role::create(['name' => $roleEnum->value]);
    }

    (new SyncPermissions)->handle();

    $action = new AssignPermissionsToRoles;

    $action->handle();

    $adminRole = Role::findByName(RoleEnum::ADMIN->value);
    expect($adminRole->permissions()->count())->toBeGreaterThan(0);
});
