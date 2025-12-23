<?php

declare(strict_types=1);

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    // Ensure roles and permissions exist for testing
    foreach (RoleEnum::cases() as $roleEnum) {
        Role::query()->firstOrCreate(['name' => $roleEnum->value]);
    }

    foreach (PermissionEnum::cases() as $permission) {
        Permission::query()->firstOrCreate(['name' => $permission->value]);
    }
});

it('renders pos screen for a user with access_pos permission', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-pos-ui';
    $store = App\Models\Store::factory()->active()->create(['created_by' => $user->id]);
    App\Models\PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $store->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)
        ->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->get(route('pos.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page->component('pos/index'));
});

it('denies pos screen for a user without access_pos permission', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, 'test-device-pos-ui-denied')
        ->get(route('pos.index'));

    $response->assertForbidden();
});
