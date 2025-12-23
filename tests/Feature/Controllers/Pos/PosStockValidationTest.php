<?php

declare(strict_types=1);

use App\Enums\PaymentMethodEnum;
use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Middleware\EnsurePosDeviceCookie;
use App\Models\InventoryLayer;
use App\Models\PosRegister;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tax;
use App\Models\User;
use App\Settings\InventorySettings;
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

it('blocks adding product to cart when insufficient stock', function (): void {
    /** @var InventorySettings $settings */
    $settings = resolve(InventorySettings::class);
    $settings->auto_deduct_stock = true;
    $settings->save();

    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-stock-validation';
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

    // Create inventory layer with only 2 items in stock
    InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'unit_cost' => 500,
        'received_qty' => 2,
        'remaining_qty' => 2,
        'received_at' => now(),
    ]);

    // Try to add 3 items (more than available)
    $this->actingAs($user)
        ->withCookie(EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->post(route('pos.cart.items.store'), ['product_id' => $product->id, 'quantity' => 3])
        ->assertStatus(500); // InvalidArgumentException -> 500
});

it('allows adding product to cart when stock is sufficient', function (): void {
    /** @var InventorySettings $settings */
    $settings = resolve(InventorySettings::class);
    $settings->auto_deduct_stock = true;
    $settings->save();

    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-stock-validation-ok';
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
        'unit_cost' => 500,
        'received_qty' => 10,
        'remaining_qty' => 10,
        'received_at' => now(),
    ]);

    $this->actingAs($user)
        ->withCookie(EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->post(route('pos.cart.items.store'), ['product_id' => $product->id, 'quantity' => 5])
        ->assertCreated();
});

it('blocks payment when stock becomes insufficient after adding to cart', function (): void {
    /** @var InventorySettings $settings */
    $settings = resolve(InventorySettings::class);
    $settings->auto_deduct_stock = true;
    $settings->save();

    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-stock-validation-payment';
    $store = Store::factory()->active()->create(['created_by' => $user->id]);

    PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $store->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $tax = Tax::factory()->percentage(0)->active()->create([
        'tax_type' => App\Enums\TaxTypeEnum::PERCENTAGE->value,
        'rate' => 0,
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
        'unit_cost' => 500,
        'received_qty' => 5,
        'remaining_qty' => 5,
        'received_at' => now(),
    ]);

    // Add 3 to cart (allowed)
    $this->actingAs($user)
        ->withCookie(EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->post(route('pos.cart.items.store'), ['product_id' => $product->id, 'quantity' => 3])
        ->assertCreated();

    // Simulate someone else buying 3 units (stock drops to 2)
    $layer = InventoryLayer::query()
        ->where('product_id', $product->id)
        ->where('store_id', $store->id)
        ->first();
    $layer->update(['remaining_qty' => 2]);

    // Now payment should fail (need 3, only 2 available)
    $this->actingAs($user)
        ->withCookie(EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->post(route('pos.payments.store'), [
            'store_id' => $store->id,
            'amount' => 3000,
            'method' => PaymentMethodEnum::CASH->value,
        ])
        ->assertStatus(500);
});

it('returns available_stock in product search response', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-stock-search';
    $store = Store::factory()->active()->create(['created_by' => $user->id]);

    PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $store->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $product = Product::factory()->create([
        'name' => 'Test Product',
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'remaining_qty' => 15,
        'received_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->withCookie(EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->get(route('pos.products.index', ['query' => 'Test']))
        ->assertOk()
        ->json();

    expect($response['data'])->toBeArray()
        ->and($response['data'][0]['available_stock'])->toBe(15);
});
