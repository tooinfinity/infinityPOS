<?php

declare(strict_types=1);

use App\Data\Users\UserData;
use App\Models\Role;
use App\Models\User;

it('builds UserData from  without needing password or role', function (): void {
    $role = Role::factory()->create();
    $user = User::factory()->create();
    $user->assignRole($role->id);
    $user->load('roles');

    $data = UserData::from($user);

    expect($data->id)->toBe($user->id)
        ->and($data->name)->toBe($user->name)
        ->and($data->email)->toBe($user->email)
        ->and($data->roles)->toBeArray()
        ->and($data->created_at)->toBe($user->created_at->toDateTimeString());
});
