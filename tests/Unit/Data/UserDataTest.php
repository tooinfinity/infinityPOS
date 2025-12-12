<?php

declare(strict_types=1);

use App\Data\UserData;
use App\Models\Role;
use App\Models\User;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

it('builds UserData from  without needing password or role', function (): void {
    $role = Role::factory()->create();
    $user = User::factory()->create();
    $user->assignRole($role->id);
    $user->load('roles');

    $data = UserData::from($user);

    expect($data->id)->toBe($user->id)
        ->and($data->name)->toBe($user->name)
        ->and($data->email)->toBe($user->email)
        ->and($data->roles)->toBeInstanceOf(Lazy::class)
        ->and($data->roles->resolve())->toBeInstanceOf(DataCollection::class)
        ->and($data->roles->resolve()->count())->toBe(1)
        ->and($data->created_at)->toBe($user->created_at->toDateTimeString())
        ->and($data->updated_at)->toBe($user->updated_at->toDateTimeString());
});
