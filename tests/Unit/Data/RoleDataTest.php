<?php

declare(strict_types=1);

use App\Data\RoleData;
use App\Models\Role;

it('transforms a role model into RoleData', function (): void {
    /** @var Role $role */
    $role = Role::factory()->create(['name' => 'admin']);

    $data = RoleData::from($role);

    expect($data)
        ->toBeInstanceOf(RoleData::class)
        ->name->toBe('admin');
});
