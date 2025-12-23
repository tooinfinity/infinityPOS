<?php

declare(strict_types=1);

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Middleware\EnsurePosDeviceCookie;
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

it('clears current device cart from register setup screen', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-register-clear-cart';
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

    // Add inventory so stock validation passes
    App\Models\InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'remaining_qty' => 100,
        'received_at' => now(),
    ]);

    // Create cart
    $this->actingAs($user)
        ->withCookie(EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->post(route('pos.cart.items.store'), ['product_id' => $product->id, 'quantity' => 1])
        ->assertCreated();

    $register = PosRegister::query()->where('device_id', $deviceId)->firstOrFail();
    expect($register->draft_sale_id)->not->toBeNull();

    // Clear via register endpoint
    $this->actingAs($user)
        ->withCookie(EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->delete(route('pos.register.cart.clear'))
        ->assertRedirect();

    $register->refresh();
    expect($register->draft_sale_id)->toBeNull();

    $this->actingAs($user)
        ->withCookie(EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->get(route('pos.cart.show'))
        ->assertOk()
        ->assertJsonPath('data.items', []);
});
