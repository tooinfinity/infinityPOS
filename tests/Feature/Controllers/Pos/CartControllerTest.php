<?php

declare(strict_types=1);

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
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

it('shows empty cart successfully', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-show-cart';
    $store = Store::factory()->active()->create(['created_by' => $user->id]);

    PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $store->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)
        ->withCookie('pos_device_id', $deviceId)
        ->get(route('pos.cart.show'));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'items',
                'totals' => [
                    'subtotal',
                    'discount_total',
                    'tax_total',
                    'total',
                ],
            ],
        ])
        ->assertJsonPath('data.items', [])
        ->assertJsonPath('data.totals.subtotal', 0)
        ->assertJsonPath('data.totals.total', 0);
});

it('shows cart with items', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-show-cart-with-items';
    $store = Store::factory()->active()->create(['created_by' => $user->id]);

    PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $store->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $product = App\Models\Product::factory()->create([
        'price' => 1000,
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    App\Models\InventoryLayer::factory()->create([
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
            'quantity' => 2,
        ])
        ->assertCreated();

    // Show cart
    $response = $this->actingAs($user)
        ->withCookie('pos_device_id', $deviceId)
        ->get(route('pos.cart.show'));

    $response->assertOk()
        ->assertJsonCount(1, 'data.items')
        ->assertJsonPath('data.items.0.product_id', $product->id)
        ->assertJsonPath('data.items.0.quantity', 2)
        ->assertJsonPath('data.items.0.unit_price', 1000)
        ->assertJsonPath('data.items.0.line_subtotal', 2000)
        ->assertJsonPath('data.totals.subtotal', 2000);
});

it('requires authentication', function (): void {
    $response = $this->get(route('pos.cart.show'));

    $response->assertRedirect(route('login'));
});

it('requires access_pos permission', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withCookie('pos_device_id', 'test-device')
        ->get(route('pos.cart.show'));

    $response->assertForbidden();
});
