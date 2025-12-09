<?php

declare(strict_types=1);

use App\Data\PermissionData;
use App\Models\Permission;

it('transforms a permission model into PermissionData', function () {
    $permission = Permission::factory()->create();

    $data = PermissionData::from($permission);

    expect($data)
        ->toBeInstanceOf(PermissionData::class)
        ->name->toBe($permission->name);
});
