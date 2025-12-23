<?php

declare(strict_types=1);

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\Product;
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

it('searches products by query (name/sku/barcode) and returns active only', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $match = Product::factory()->create([
        'name' => 'Coca Cola 330ml',
        'sku' => 'COKE-330',
        'barcode' => '1112223334445',
        'is_active' => true,
    ]);

    Product::factory()->create([
        'name' => 'Coca Cola Zero',
        'sku' => 'COKE-ZERO',
        'barcode' => '9998887776665',
        'is_active' => false,
    ]);

    $deviceId = 'test-device-pos-search';
    $store = App\Models\Store::factory()->active()->create(['created_by' => $user->id]);
    App\Models\PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $store->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)
        ->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->get(route('pos.products.index', ['query' => 'Coca']));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $match->id);
});

it('searches products by barcode (exact match)', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-pos-search';
    $store = App\Models\Store::factory()->active()->create(['created_by' => $user->id]);
    App\Models\PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $store->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $match = Product::factory()->create([
        'barcode' => '1234567890123',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->get(route('pos.products.index', ['barcode' => '1234567890123']));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $match->id);
});

it('returns empty list when no query provided', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-pos-search';
    $store = App\Models\Store::factory()->active()->create(['created_by' => $user->id]);
    App\Models\PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $store->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)
        ->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->get(route('pos.products.index'));

    $response->assertOk()->assertExactJson(['data' => []]);
});
