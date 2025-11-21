<?php

declare(strict_types=1);

use App\Actions\CreatePermissions;
use App\Enums\PermissionEnum;
use Spatie\Permission\Models\Permission;

it('creates all permissions from enum', function (): void {
    $action = new CreatePermissions;

    $permissions = $action->handle();

    expect($permissions)->toBeArray()
        ->and(count($permissions))->toBe(count(PermissionEnum::cases()));

    foreach (PermissionEnum::cases() as $permissionEnum) {
        expect($permissions)->toHaveKey($permissionEnum->value)
            ->and($permissions[$permissionEnum->value])->toBeInstanceOf(Permission::class)
            ->and($permissions[$permissionEnum->value]->name)->toBe($permissionEnum->value)
            ->and($permissions[$permissionEnum->value]->guard_name)->toBe('web');
    }
});

it('does not create duplicate permissions', function (): void {
    $action = new CreatePermissions;

    $firstRun = $action->handle();
    $secondRun = $action->handle();

    expect(count($firstRun))->toBe(count($secondRun));

    foreach (PermissionEnum::cases() as $permissionEnum) {
        $permission = Permission::findByName($permissionEnum->value);
        expect($permission)->not->toBeNull();
    }
});

it('creates permissions with custom guard name', function (): void {
    $action = new CreatePermissions;

    $permissions = $action->handle('api');

    foreach (PermissionEnum::cases() as $permissionEnum) {
        $permission = Permission::query()
            ->where('name', $permissionEnum->value)
            ->where('guard_name', 'api')
            ->first();

        expect($permission)->not->toBeNull();
    }
});
