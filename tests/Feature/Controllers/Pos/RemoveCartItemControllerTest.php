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

it('removes cart item successfully', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-remove-cart-item';
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

    // Add item first
    $addResponse = $this->actingAs($user)
        ->withCookie('pos_device_id', $deviceId)
        ->post(route('pos.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

    $addResponse->assertCreated()
        ->assertJsonCount(1, 'data.items');

    $lineId = $addResponse->json('data.items.0.line_id');

    // Remove item
    $response = $this->actingAs($user)
        ->withCookie('pos_device_id', $deviceId)
        ->delete(route('pos.cart.items.destroy', ['lineId' => $lineId]));

    $response->assertOk()
        ->assertJsonPath('data.items', [])
        ->assertJsonPath('data.totals.subtotal', 0);
});

it('requires authentication', function (): void {
    $response = $this->delete(route('pos.cart.items.destroy', ['lineId' => 'some-line-id']));

    $response->assertRedirect(route('login'));
});

it('requires access_pos permission', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withCookie('pos_device_id', 'test-device')
        ->delete(route('pos.cart.items.destroy', ['lineId' => 'some-line-id']));

    $response->assertForbidden();
});

it('handles removing item and shows remaining items', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-remove-show-remaining';
    $store = Store::factory()->active()->create(['created_by' => $user->id]);

    PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $store->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $product1 = Product::factory()->create([
        'price' => 1000,
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $product2 = Product::factory()->create([
        'price' => 2000,
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    InventoryLayer::factory()->create([
        'product_id' => $product1->id,
        'store_id' => $store->id,
        'remaining_qty' => 100,
        'received_at' => now(),
    ]);

    InventoryLayer::factory()->create([
        'product_id' => $product2->id,
        'store_id' => $store->id,
        'remaining_qty' => 100,
        'received_at' => now(),
    ]);

    // Add both items
    $addResponse1 = $this->actingAs($user)
        ->withCookie('pos_device_id', $deviceId)
        ->post(route('pos.cart.items.store'), [
            'product_id' => $product1->id,
            'quantity' => 1,
        ]);

    $this->actingAs($user)
        ->withCookie('pos_device_id', $deviceId)
        ->post(route('pos.cart.items.store'), [
            'product_id' => $product2->id,
            'quantity' => 1,
        ]);

    $lineId1 = $addResponse1->json('data.items.0.line_id');

    // Remove first item, should show second item
    $response = $this->actingAs($user)
        ->withCookie('pos_device_id', $deviceId)
        ->delete(route('pos.cart.items.destroy', ['lineId' => $lineId1]));

    $response->assertOk()
        ->assertJsonCount(1, 'data.items')
        ->assertJsonPath('data.items.0.product_id', $product2->id)
        ->assertJsonPath('data.totals.subtotal', 2000);
});
