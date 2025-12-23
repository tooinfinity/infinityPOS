<?php

declare(strict_types=1);

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Enums\TaxTypeEnum;
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

it('applies discount to cart successfully', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-apply-discount';
    $store = Store::factory()->active()->create(['created_by' => $user->id]);

    PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $store->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $tax = Tax::factory()->create([
        'tax_type' => TaxTypeEnum::PERCENTAGE,
        'rate' => 10,
        'is_active' => true,
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

    // Add item first
    $this->actingAs($user)
        ->withCookie('pos_device_id', $deviceId)
        ->post(route('pos.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ])
        ->assertCreated();

    // Apply discount
    $response = $this->actingAs($user)
        ->withCookie('pos_device_id', $deviceId)
        ->put(route('pos.cart.discount.update'), [
            'discount' => 500,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.totals.subtotal', 2000)
        ->assertJsonPath('data.totals.discount_total', 500)
        ->assertJsonPath('data.totals.tax_total', 150)
        ->assertJsonPath('data.totals.total', 1650);
});

it('requires authentication', function (): void {
    $response = $this->put(route('pos.cart.discount.update'), [
        'discount' => 500,
    ]);

    $response->assertRedirect(route('login'));
});

it('requires access_pos permission', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withCookie('pos_device_id', 'test-device')
        ->put(route('pos.cart.discount.update'), [
            'discount' => 500,
        ]);

    $response->assertForbidden();
});

it('validates discount is required', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-discount-validation';
    $store = Store::factory()->active()->create(['created_by' => $user->id]);

    PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $store->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)
        ->withCookie('pos_device_id', $deviceId)
        ->put(route('pos.cart.discount.update'), []);

    $response->assertSessionHasErrors(['discount']);
});

it('accepts zero discount', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-zero-discount';
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

    $this->actingAs($user)
        ->withCookie('pos_device_id', $deviceId)
        ->post(route('pos.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])
        ->assertCreated();

    $response = $this->actingAs($user)
        ->withCookie('pos_device_id', $deviceId)
        ->put(route('pos.cart.discount.update'), [
            'discount' => 0,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.totals.discount_total', 0);
});
