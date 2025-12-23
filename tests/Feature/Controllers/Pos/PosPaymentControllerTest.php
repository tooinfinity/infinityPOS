<?php

declare(strict_types=1);

use App\Enums\PaymentMethodEnum;
use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Enums\SaleStatusEnum;
use App\Enums\TaxTypeEnum;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
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

it('processes POS payment: creates sale, payment, completes sale, clears cart', function (): void {
    $deviceId = 'test-device-pos-payment';

    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $store = Store::factory()->active()->create(['created_by' => $user->id]);
    // Configure register for this device
    App\Models\PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $store->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $tax = Tax::factory()->percentage(10)->active()->create([
        'tax_type' => TaxTypeEnum::PERCENTAGE->value,
        'rate' => 10,
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $product = Product::factory()->create([
        'price' => 1000,
        'cost' => 400,
        'tax_id' => $tax->id,
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

    // Add to cart first
    $this->actingAs($user)->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->post(route('pos.cart.items.store'), ['product_id' => $product->id, 'quantity' => 2])
        ->assertCreated();

    // Apply a cart discount of 200 => discounted subtotal 1800; tax 10% => 180; total 1980
    $this->actingAs($user)->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->put(route('pos.cart.discount.update'), ['discount' => 200])
        ->assertOk();

    $response = $this->actingAs($user)->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)->post(route('pos.payments.store'), [
        'store_id' => $store->id,
        'amount' => 1980,
        'method' => PaymentMethodEnum::CASH->value,
        'reference' => 'POSPAY-1',
        'notes' => 'POS payment',
    ]);

    $response->assertCreated();

    $saleId = $response->json('data.sale_id');
    $sale = Sale::query()->with(['items', 'payments'])->findOrFail($saleId);

    expect($sale->store_id)->toBe($store->id)
        ->and($sale->status)->toBe(SaleStatusEnum::COMPLETED)
        ->and($sale->discount)->toBe(200)
        ->and($sale->total)->toBe(1980);

    expect($sale->items)->toHaveCount(1)
        ->and($sale->items->first()->quantity)->toBe(2);

    $this->assertDatabaseHas('payments', [
        'related_type' => Sale::class,
        'related_id' => $sale->id,
        'amount' => 1980,
        'method' => PaymentMethodEnum::CASH->value,
    ]);

    // Stock movements created
    expect(StockMovement::query()->where('source_type', Sale::class)->where('source_id', $sale->id)->count())
        ->toBe(1);

    // Cart cleared
    $this->actingAs($user)->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->get(route('pos.cart.show'))
        ->assertOk()
        ->assertJsonPath('data.items', []);
});

it('denies POS payment endpoint without access_pos', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, 'test-device-pos-payment-forbidden')
        ->post(route('pos.payments.store'), [
            'store_id' => 1,
            'amount' => 100,
            'method' => PaymentMethodEnum::CASH->value,
        ])
        ->assertForbidden();
});
