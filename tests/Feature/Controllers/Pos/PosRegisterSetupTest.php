<?php

declare(strict_types=1);

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Middleware\EnsurePosDeviceCookie;
use App\Models\PosRegister;
use App\Models\Store;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    foreach (RoleEnum::cases() as $roleEnum) {
        Role::query()->firstOrCreate(['name' => $roleEnum->value]);
    }

    foreach (PermissionEnum::cases() as $permission) {
        Permission::query()->firstOrCreate(['name' => $permission->value]);
    }
});

it('redirects pos.index to register setup when device is not configured', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-setup-redirect';

    $this->actingAs($user)
        ->withCookie(EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->get(route('pos.index'))
        ->assertRedirect(route('pos.register.edit'));
});

it('can configure device register and then access pos.index', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $store = Store::factory()->active()->create();
    $deviceId = 'test-device-setup-configure';

    // Setup page renders
    $this->actingAs($user)
        ->withCookie(EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->get(route('pos.register.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('pos/register'));

    // Configure
    $this->actingAs($user)
        ->withCookie(EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->put(route('pos.register.update'), [
            'store_id' => $store->id,
            'name' => 'Front Register',
        ])
        ->assertRedirect(route('pos.index'));

    $register = PosRegister::query()->where('device_id', $deviceId)->first();
    expect($register)->not->toBeNull()
        ->and($register->store_id)->toBe($store->id)
        ->and($register->name)->toBe('Front Register')
        ->and($register->configured_at)->not->toBeNull();

    // Now can access pos
    $this->actingAs($user)
        ->withCookie(EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->get(route('pos.index'))
        ->assertOk();
});
