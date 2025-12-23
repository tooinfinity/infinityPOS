<?php

declare(strict_types=1);

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\InventoryLayer;
use App\Models\PosRegister;
use App\Models\Product;
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

it('clears cart from register setup screen', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-clear-register-cart';
    $store = Store::factory()->active()->create(['created_by' => $user->id]);

    PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $store->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $product = Product::factory()->create([
        'price' => 1000,
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'remaining_qty' => 100,
        'received_at' => now(),
    ]);

    // Add item to cart
    $this->actingAs($user)
        ->withCookie('pos_device_id', $deviceId)
        ->post(route('pos.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])
        ->assertCreated();

    $register = PosRegister::query()->where('device_id', $deviceId)->firstOrFail();
    expect($register->draft_sale_id)->not->toBeNull();

    // Clear via register endpoint (should redirect back)
    $response = $this->actingAs($user)
        ->withCookie('pos_device_id', $deviceId)
        ->delete(route('pos.register.cart.clear'));

    $response->assertRedirect();

    // Verify cart is cleared
    $register->refresh();
    expect($register->draft_sale_id)->toBeNull();

    $this->actingAs($user)
        ->withCookie('pos_device_id', $deviceId)
        ->get(route('pos.cart.show'))
        ->assertOk()
        ->assertJsonPath('data.items', []);
});

it('requires authentication', function (): void {
    $response = $this->delete(route('pos.register.cart.clear'));

    $response->assertRedirect(route('login'));
});

it('requires access_pos permission', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withCookie('pos_device_id', 'test-device')
        ->delete(route('pos.register.cart.clear'));

    $response->assertForbidden();
});

it('is idempotent when cart is already empty', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-clear-register-empty';
    $store = Store::factory()->active()->create(['created_by' => $user->id]);

    PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $store->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)
        ->withCookie('pos_device_id', $deviceId)
        ->delete(route('pos.register.cart.clear'));

    $response->assertRedirect();
});
