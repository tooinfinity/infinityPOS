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

it('allows access_pos user to use cart endpoints', function (): void {
    $deviceId = 'test-device-pos-endpoints';

    $user = User::factory()->create();
    $store = App\Models\Store::factory()->active()->create(['created_by' => $user->id]);
    App\Models\PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $store->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $tax = App\Models\Tax::factory()->percentage(10)->active()->create([
        'tax_type' => App\Enums\TaxTypeEnum::PERCENTAGE->value,
        'rate' => 10,
        'is_active' => true,
    ]);

    $product = Product::factory()->create([
        'price' => 1000,
        'tax_id' => $tax->id,
        'is_active' => true,
    ]);

    // Add inventory so stock validation passes
    App\Models\InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'remaining_qty' => 100,
        'received_at' => now(),
    ]);

    // Starts empty
    $this->actingAs($user)->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->get(route('pos.cart.show'))
        ->assertOk()
        ->assertJsonPath('data.items', [])
        ->assertJsonPath('data.totals.subtotal', 0)
        ->assertJsonPath('data.totals.total', 0);

    // Add item
    $addResponse = $this->actingAs($user)->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->post(route('pos.cart.items.store'), ['product_id' => $product->id, 'quantity' => 2]);

    $addResponse->assertCreated()
        ->assertJsonCount(1, 'data.items')
        ->assertJsonPath('data.totals.subtotal', 2000)
        ->assertJsonPath('data.totals.tax_total', 200)
        ->assertJsonPath('data.totals.total', 2200);

    $lineId = $addResponse->json('data.items.0.line_id');
    expect($lineId)->toBeString();

    // Apply cart discount
    $this->actingAs($user)->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->put(route('pos.cart.discount.update'), ['discount' => 500])
        ->assertOk()
        ->assertJsonPath('data.totals.discount_total', 500)
        ->assertJsonPath('data.totals.tax_total', 150)
        ->assertJsonPath('data.totals.total', 1650);

    // Update quantity (discount stays, tax recalculates)
    $this->actingAs($user)->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->patch(route('pos.cart.items.update', ['lineId' => $lineId]), ['quantity' => 3])
        ->assertOk()
        ->assertJsonPath('data.items.0.quantity', 3)
        ->assertJsonPath('data.totals.subtotal', 3000)
        ->assertJsonPath('data.totals.discount_total', 500)
        ->assertJsonPath('data.totals.tax_total', 250)
        ->assertJsonPath('data.totals.total', 2750);

    // Remove item
    $this->actingAs($user)->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->delete(route('pos.cart.items.destroy', ['lineId' => $lineId]))
        ->assertOk()
        ->assertJsonPath('data.items', [])
        ->assertJsonPath('data.totals.subtotal', 0);

    // Clear cart (idempotent)
    $this->actingAs($user)->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->delete(route('pos.cart.clear'))
        ->assertOk()
        ->assertJsonPath('data.items', [])
        ->assertJsonPath('data.totals.total', 0);
});

it('allows access_pos user to access other POS endpoints', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-pos-other';
    $store = App\Models\Store::factory()->active()->create(['created_by' => $user->id]);
    App\Models\PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $store->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->get(route('pos.products.index'))
        ->assertOk();

    // Receipt endpoint now expects a Sale route model binding.
    $sale = App\Models\Sale::factory()->create(['created_by' => $user->id]);

    $this->actingAs($user)
        ->get(route('pos.receipts.show', ['sale' => $sale->id]))
        ->assertOk();
});

it('denies POS endpoints for user without access_pos permission', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, 'test-device-pos-forbidden')
        ->get(route('pos.products.index'))
        ->assertForbidden();
    $this->actingAs($user)
        ->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, 'test-device-pos-forbidden')
        ->get(route('pos.cart.show'))
        ->assertForbidden();
    $this->actingAs($user)
        ->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, 'test-device-pos-forbidden')
        ->post(route('pos.cart.items.store'))
        ->assertForbidden();
    $this->actingAs($user)
        ->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, 'test-device-pos-forbidden')
        ->post(route('pos.payments.store'))
        ->assertForbidden();
    $sale = App\Models\Sale::factory()->create();
    $this->actingAs($user)->get(route('pos.receipts.show', ['sale' => $sale->id]))->assertForbidden();
});
