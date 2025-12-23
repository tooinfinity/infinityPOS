<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\Role;

test('it may create a role', function (): void {
    $role = Role::factory()->create([
        'name' => 'test_role',
        'guard_name' => 'web',
    ]);

    expect($role)
        ->toBeInstanceOf(Role::class)
        ->name->toBe('test_role')
        ->guard_name->toBe('web')
        ->id->toBeInt()
        ->created_at->toBeInstanceOf(DateTimeInterface::class)
        ->updated_at->toBeInstanceOf(DateTimeInterface::class);
});

test('it has fillable attributes', function (): void {
    $role = Role::factory()->create();

    expect($role->name)->toBeString();
    expect($role->guard_name)->toBeString();
});

test('it can be found by name', function (): void {
    Role::factory()->create(['name' => 'admin']);

    $found = Role::findByName('admin');

    expect($found)->toBeInstanceOf(Role::class)
        ->name->toBe('admin');
});

test('it can assign permissions', function (): void {
    $role = Role::factory()->create();
    $permission = Permission::factory()->create(['name' => 'test_permission']);

    $role->givePermissionTo($permission);

    expect($role->hasPermissionTo('test_permission'))->toBeTrue();
});

test('it can check multiple permissions', function (): void {
    $role = Role::factory()->create();
    $permission1 = Permission::factory()->create(['name' => 'permission_1']);
    $permission2 = Permission::factory()->create(['name' => 'permission_2']);

    $role->givePermissionTo([$permission1, $permission2]);

    expect($role->hasPermissionTo('permission_1'))->toBeTrue();
    expect($role->hasPermissionTo('permission_2'))->toBeTrue();
});
