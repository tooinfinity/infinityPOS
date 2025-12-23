<?php

declare(strict_types=1);

use App\Models\Permission;

test('it may create a permission', function (): void {
    $permission = Permission::factory()->create([
        'name' => 'test_permission',
        'guard_name' => 'web',
    ]);

    expect($permission)
        ->toBeInstanceOf(Permission::class)
        ->name->toBe('test_permission')
        ->guard_name->toBe('web')
        ->id->toBeInt()
        ->created_at->toBeInstanceOf(DateTimeInterface::class)
        ->updated_at->toBeInstanceOf(DateTimeInterface::class);
});

test('it has fillable attributes', function (): void {
    $permission = Permission::factory()->create();

    expect($permission->name)->toBeString();
    expect($permission->guard_name)->toBeString();
});

test('it can be found by name', function (): void {
    Permission::factory()->create(['name' => 'find_me']);

    $found = Permission::findByName('find_me');

    expect($found)->toBeInstanceOf(Permission::class)
        ->name->toBe('find_me');
});
