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

it('updates cart item quantity successfully', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-update-cart-item';
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

    $lineId = $addResponse->json('data.items.0.line_id');

    // Update quantity
    $response = $this->actingAs($user)
        ->withCookie('pos_device_id', $deviceId)
        ->patch(route('pos.cart.items.update', ['lineId' => $lineId]), [
            'quantity' => 5,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.items.0.quantity', 5)
        ->assertJsonPath('data.totals.subtotal', 5000);
});

it('requires authentication', function (): void {
    $response = $this->patch(route('pos.cart.items.update', ['lineId' => 'some-line-id']), [
        'quantity' => 5,
    ]);

    $response->assertRedirect(route('login'));
});

it('requires access_pos permission', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withCookie('pos_device_id', 'test-device')
        ->patch(route('pos.cart.items.update', ['lineId' => 'some-line-id']), [
            'quantity' => 5,
        ]);

    $response->assertForbidden();
});

it('validates quantity is required', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-update-validation';
    $store = Store::factory()->active()->create(['created_by' => $user->id]);

    PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $store->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)
        ->withCookie('pos_device_id', $deviceId)
        ->patch(route('pos.cart.items.update', ['lineId' => 'some-line-id']), []);

    $response->assertSessionHasErrors(['quantity']);
});
