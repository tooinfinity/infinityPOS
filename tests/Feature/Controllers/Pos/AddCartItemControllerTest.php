<?php

declare(strict_types=1);

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\InventoryLayer;
use App\Models\PosRegister;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tax;
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

it('adds item to cart successfully', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-add-cart-item';
    $store = Store::factory()->active()->create(['created_by' => $user->id]);

    PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $store->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $tax = Tax::factory()->percentage(10)->active()->create([
        'created_by' => $user->id,
    ]);

    $product = Product::factory()->create([
        'price' => 1000,
        'tax_id' => $tax->id,
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'remaining_qty' => 100,
        'received_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->withCookie('pos_device_id', $deviceId)
        ->post(route('pos.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

    $response->assertCreated()
        ->assertJsonCount(1, 'data.items')
        ->assertJsonPath('data.items.0.product_id', $product->id)
        ->assertJsonPath('data.items.0.quantity', 2)
        ->assertJsonPath('data.totals.subtotal', 2000);
});

it('requires authentication', function (): void {
    $product = Product::factory()->create();

    $response = $this->post(route('pos.cart.items.store'), [
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $response->assertRedirect(route('login'));
});

it('requires access_pos permission', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $response = $this->actingAs($user)
        ->withCookie('pos_device_id', 'test-device')
        ->post(route('pos.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

    $response->assertForbidden();
});

it('validates product_id is required', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-validation';
    $store = Store::factory()->active()->create(['created_by' => $user->id]);

    PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $store->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)
        ->withCookie('pos_device_id', $deviceId)
        ->post(route('pos.cart.items.store'), [
            'quantity' => 1,
        ]);

    $response->assertSessionHasErrors(['product_id']);
});

it('validates quantity is required and positive', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-quantity-validation';
    $store = Store::factory()->active()->create(['created_by' => $user->id]);

    PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $store->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $product = Product::factory()->create(['created_by' => $user->id]);

    $response = $this->actingAs($user)
        ->withCookie('pos_device_id', $deviceId)
        ->post(route('pos.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 0,
        ]);

    $response->assertSessionHasErrors(['quantity']);
});
