<?php

declare(strict_types=1);

use App\Enums\PermissionEnum;
use App\Models\User;
use App\Policies\UserPolicy;
use Spatie\Permission\Models\Permission;

beforeEach(function (): void {
    Permission::query()->firstOrCreate(['name' => PermissionEnum::VIEW_USERS->value]);
    Permission::query()->firstOrCreate(['name' => PermissionEnum::CREATE_USERS->value]);
    Permission::query()->firstOrCreate(['name' => PermissionEnum::EDIT_USERS->value]);
    Permission::query()->firstOrCreate(['name' => PermissionEnum::DELETE_USERS->value]);
});

it('allows viewing users if user has permission', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::VIEW_USERS->value);

    $policy = new UserPolicy;

    expect($policy->viewAny($user))->toBeTrue();
});

it('denies viewing users if user lacks permission', function (): void {
    $user = User::factory()->create();

    $policy = new UserPolicy;

    expect($policy->viewAny($user))->toBeFalse();
});

it('allows creating users if user has permission', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::CREATE_USERS->value);

    $policy = new UserPolicy;

    expect($policy->create($user))->toBeTrue();
});

it('denies creating users if user lacks permission', function (): void {
    $user = User::factory()->create();

    $policy = new UserPolicy;

    expect($policy->create($user))->toBeFalse();
});

it('allows updating other users if user has permission', function (): void {
    $admin = User::factory()->create();
    $admin->givePermissionTo(PermissionEnum::EDIT_USERS->value);

    $otherUser = User::factory()->create();

    $policy = new UserPolicy;

    expect($policy->update($admin, $otherUser))->toBeTrue();
});

it('denies updating other users if user lacks permission', function (): void {
    $admin = User::factory()->create();
    // No permission given

    $otherUser = User::factory()->create();

    $policy = new UserPolicy;

    expect($policy->update($admin, $otherUser))->toBeFalse();
});

it('denies updating self even with permission', function (): void {
    $admin = User::factory()->create();
    $admin->givePermissionTo(PermissionEnum::EDIT_USERS->value);

    $policy = new UserPolicy;

    expect($policy->update($admin, $admin))->toBeFalse();
});

it('allows deleting other users if user has permission', function (): void {
    $admin = User::factory()->create();
    $admin->givePermissionTo(PermissionEnum::DELETE_USERS->value);

    $otherUser = User::factory()->create();

    $policy = new UserPolicy;

    expect($policy->delete($admin, $otherUser))->toBeTrue();
});

it('denies deleting other users if user lacks permission', function (): void {
    $admin = User::factory()->create();

    $otherUser = User::factory()->create();

    $policy = new UserPolicy;

    expect($policy->delete($admin, $otherUser))->toBeFalse();
});

it('denies deleting self even with permission', function (): void {
    $admin = User::factory()->create();
    $admin->givePermissionTo(PermissionEnum::DELETE_USERS->value);

    $policy = new UserPolicy;

    expect($policy->delete($admin, $admin))->toBeFalse();
});
