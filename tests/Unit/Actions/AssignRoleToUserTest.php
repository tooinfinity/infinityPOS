<?php

declare(strict_types=1);

use App\Actions\AssignRoleToUser;
use App\Actions\CreateRoles;
use App\Enums\RoleEnum;
use App\Models\User;

it('assigns a role to a user', function (): void {
    (new CreateRoles)->handle();

    $user = User::factory()->create();
    $action = new AssignRoleToUser;

    $result = $action->handle($user, RoleEnum::ADMIN);

    expect($result)->toBe($user)
        ->and($user->hasRole(RoleEnum::ADMIN->value))->toBeTrue();
});

it('assigns manager role to a user', function (): void {
    (new CreateRoles)->handle();

    $user = User::factory()->create();
    $action = new AssignRoleToUser;

    $action->handle($user, RoleEnum::MANAGER);

    expect($user->hasRole(RoleEnum::MANAGER->value))->toBeTrue();
});

it('assigns cashier role to a user', function (): void {
    (new CreateRoles)->handle();

    $user = User::factory()->create();
    $action = new AssignRoleToUser;

    $action->handle($user, RoleEnum::CASHIER);

    expect($user->hasRole(RoleEnum::CASHIER->value))->toBeTrue();
});

it('throws exception when role does not exist', function (): void {
    $user = User::factory()->create();
    $action = new AssignRoleToUser;

    expect(fn (): User => $action->handle($user, RoleEnum::ADMIN))
        ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
});
