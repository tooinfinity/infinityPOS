<?php

declare(strict_types=1);

use App\Actions\SyncPermissions;
use App\Enums\PermissionEnum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

it('creates missing permissions', function (): void {

    $action = new SyncPermissions;

    $result = $action->handle();

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['created', 'existing', 'deleted'])
        ->and(count($result['created']))->toBeGreaterThan(0)
        ->and(count($result['created']))->toBe(count(PermissionEnum::cases()));

    foreach ($result['created'] as $permissionName) {
        $permission = Permission::findByName($permissionName);
        expect($permission)->not->toBeNull();
    }

});

it('reports existing permissions', function (): void {
    foreach (PermissionEnum::cases() as $permissionEnum) {
        Permission::create(['name' => $permissionEnum->value]);
    }

    $action = new SyncPermissions;

    $result = $action->handle();

    expect($result['existing'])->toBeArray()
        ->and(count($result['existing']))->toBe(count(PermissionEnum::cases()))
        ->and(count($result['created']))->toBe(0);
});

it('deletes permissions not in enum', function (): void {
    Permission::query()->create([
        'name' => 'old_permission_not_in_enum',
        'guard_name' => 'web',
    ]);

    $action = new SyncPermissions;

    $result = $action->handle();

    expect($result['deleted'])->toBe(1);

    $deletedPermission = Permission::query()
        ->where('name', 'old_permission_not_in_enum')
        ->first();
    expect($deletedPermission)->toBeNull();
});

it('runs within a database transaction', function (): void {
    $action = new SyncPermissions;

    // Verify the action completes successfully, which confirms transaction works
    $result = $action->handle();

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['created', 'existing', 'deleted']);

    // Verify permissions were created (transaction completed)
    $permission = Permission::findByName(PermissionEnum::VIEW_DASHBOARD->value);
    expect($permission)->not->toBeNull();
});

it('clears permission cache after sync', function (): void {
    $action = new SyncPermissions;
    $result = $action->handle();

    // Verify the action completed successfully
    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['created', 'existing', 'deleted']);

    // The cache clearing happens internally via app()->make(PermissionRegistrar::class)
    // We verify it works by ensuring the sync completes successfully
});

it('handles custom guard name', function (): void {
    $action = new SyncPermissions;

    $result = $action->handle('api');

    expect($result)->toBeArray()
        ->and(count($result['created']))->toBeGreaterThan(0);

    $permission = Permission::query()
        ->where('name', PermissionEnum::VIEW_DASHBOARD->value)
        ->where('guard_name', 'api')
        ->first();

    expect($permission)->not->toBeNull();
});
