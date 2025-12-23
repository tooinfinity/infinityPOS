<?php

declare(strict_types=1);

use App\Enums\MoneyboxTypeEnum;
use App\Enums\PermissionEnum;
use App\Enums\SaleStatusEnum;
use App\Models\Moneybox;
use App\Models\PosRegister;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\User;
use App\Services\Pos\PosConfig;

beforeEach(function (): void {
    $this->user = User::factory()->create();

    // Create permission if it doesn't exist
    $permission = App\Models\Permission::query()->firstOrCreate([
        'name' => PermissionEnum::ACCESS_POS->value,
        'guard_name' => 'web',
    ]);

    $this->user->givePermissionTo($permission);
    $this->actingAs($this->user);
});

test('it shows register setup page without existing register', function (): void {
    $store = Store::factory()->create(['is_active' => true]);
    $moneybox = Moneybox::factory()->create([
        'is_active' => true,
        'type' => MoneyboxTypeEnum::CASH_REGISTER,
    ]);

    // Test with a device cookie but no register created yet
    $response = $this->withCookie(PosConfig::DEVICE_COOKIE_NAME, 'new-device-no-register')
        ->get(route('pos.register.edit'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('pos/register')
        ->has('stores')
        ->has('moneyboxes')
        ->where('register', null)
    );
});

test('it shows register setup page with empty device cookie', function (): void {
    $store = Store::factory()->create(['is_active' => true]);
    $moneybox = Moneybox::factory()->create([
        'is_active' => true,
        'type' => MoneyboxTypeEnum::CASH_REGISTER,
    ]);

    // Test with empty device cookie - covers line 28 (the null case)
    $response = $this->withCookie(PosConfig::DEVICE_COOKIE_NAME, '')
        ->get(route('pos.register.edit'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('pos/register')
        ->has('stores')
        ->has('moneyboxes')
        ->where('register', null)
    );
});

test('it shows register setup page with existing register', function (): void {
    $store = Store::factory()->create(['is_active' => true]);
    $moneybox = Moneybox::factory()->create([
        'is_active' => true,
        'type' => MoneyboxTypeEnum::CASH_REGISTER,
    ]);

    $register = PosRegister::factory()->create([
        'device_id' => 'test-device',
        'name' => 'Test Register',
        'store_id' => $store->id,
        'moneybox_id' => $moneybox->id,
        'configured_at' => now(),
    ]);

    $response = $this->withCookie(PosConfig::DEVICE_COOKIE_NAME, 'test-device')
        ->get(route('pos.register.edit'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('pos/register')
        ->has('stores')
        ->has('moneyboxes')
        ->where('register.name', 'Test Register')
        ->where('register.store_id', $store->id)
        ->where('register.moneybox_id', $moneybox->id)
        ->where('register.is_configured', true)
    );
});

test('it shows register with null moneybox', function (): void {
    $store = Store::factory()->create(['is_active' => true]);

    $register = PosRegister::factory()->create([
        'device_id' => 'test-device',
        'name' => 'Test Register',
        'store_id' => $store->id,
        'moneybox_id' => null,
        'configured_at' => now(),
    ]);

    $response = $this->withCookie(PosConfig::DEVICE_COOKIE_NAME, 'test-device')
        ->get(route('pos.register.edit'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('register.moneybox_id', null)
    );
});

test('it creates new register on update', function (): void {
    $store = Store::factory()->create();
    $moneybox = Moneybox::factory()->create([
        'type' => MoneyboxTypeEnum::CASH_REGISTER,
    ]);

    $response = $this->withCookie(PosConfig::DEVICE_COOKIE_NAME, 'new-device')
        ->put(route('pos.register.update'), [
            'name' => 'New Register',
            'store_id' => $store->id,
            'moneybox_id' => $moneybox->id,
        ]);

    $response->assertRedirect(route('pos.index'));

    $register = PosRegister::query()->where('device_id', 'new-device')->first();
    expect($register)
        ->not->toBeNull()
        ->name->toBe('New Register')
        ->store_id->toBe($store->id)
        ->moneybox_id->toBe($moneybox->id)
        ->configured_at->not->toBeNull();
});

test('it updates existing register', function (): void {
    $store1 = Store::factory()->create();
    $store2 = Store::factory()->create();

    $register = PosRegister::factory()->create([
        'device_id' => 'existing-device',
        'name' => 'Old Name',
        'store_id' => $store1->id,
        'configured_at' => null,
    ]);

    $response = $this->withCookie(PosConfig::DEVICE_COOKIE_NAME, 'existing-device')
        ->put(route('pos.register.update'), [
            'name' => 'Updated Name',
            'store_id' => $store2->id,
            'moneybox_id' => null,
        ]);

    $response->assertRedirect(route('pos.index'));

    $register->refresh();
    expect($register)
        ->name->toBe('Updated Name')
        ->store_id->toBe($store2->id)
        ->configured_at->not->toBeNull();
});

test('it clears cart when store changes', function (): void {
    $store1 = Store::factory()->create();
    $store2 = Store::factory()->create();
    $product = Product::factory()->create();

    $register = PosRegister::factory()->create([
        'device_id' => 'test-device',
        'store_id' => $store1->id,
    ]);

    // Create a draft sale (cart)
    $sale = Sale::factory()->create([
        'store_id' => $store1->id,
        'status' => SaleStatusEnum::PENDING,
    ]);

    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    $register->update(['draft_sale_id' => $sale->id]);

    // Update register to different store
    $response = $this->withCookie(PosConfig::DEVICE_COOKIE_NAME, 'test-device')
        ->put(route('pos.register.update'), [
            'name' => 'Register',
            'store_id' => $store2->id,
            'moneybox_id' => null,
        ]);

    $response->assertRedirect(route('pos.index'));

    // Cart should be cleared
    expect(Sale::query()->find($sale->id))->toBeNull();

    $register->refresh();
    expect($register->draft_sale_id)->toBeNull();
});

test('it does not clear cart when store remains the same', function (): void {
    $store = Store::factory()->create();
    $product = Product::factory()->create();

    $register = PosRegister::factory()->create([
        'device_id' => 'test-device',
        'store_id' => $store->id,
    ]);

    // Create a draft sale (cart)
    $sale = Sale::factory()->create([
        'store_id' => $store->id,
        'status' => SaleStatusEnum::PENDING,
    ]);

    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    $register->update(['draft_sale_id' => $sale->id]);

    // Update register but keep same store
    $response = $this->withCookie(PosConfig::DEVICE_COOKIE_NAME, 'test-device')
        ->put(route('pos.register.update'), [
            'name' => 'Updated Name',
            'store_id' => $store->id,
            'moneybox_id' => null,
        ]);

    $response->assertRedirect(route('pos.index'));

    // Cart should NOT be cleared
    expect(Sale::query()->find($sale->id))->not->toBeNull();
});

test('it redirects to register setup when device id is missing', function (): void {
    $store = Store::factory()->create();

    // Without a device cookie, the middleware will redirect to setup
    $response = $this->put(route('pos.register.update'), [
        'name' => 'Register',
        'store_id' => $store->id,
        'moneybox_id' => null,
    ]);

    // The middleware creates a device cookie and redirects to register setup
    $response->assertRedirect();
});

test('it filters only active stores', function (): void {
    Store::factory()->create(['is_active' => true, 'name' => 'Active Store']);
    Store::factory()->create(['is_active' => false, 'name' => 'Inactive Store']);

    $response = $this->get(route('pos.register.edit'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('stores', 1)
    );
});

test('it filters only cash register moneyboxes', function (): void {
    Moneybox::factory()->create([
        'is_active' => true,
        'type' => MoneyboxTypeEnum::CASH_REGISTER,
        'name' => 'Cash Register',
    ]);
    Moneybox::factory()->create([
        'is_active' => true,
        'type' => MoneyboxTypeEnum::BANK_ACCOUNT,
        'name' => 'Bank Account',
    ]);

    $response = $this->get(route('pos.register.edit'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('moneyboxes', 1)
    );
});

test('it preserves configured_at when updating already configured register', function (): void {
    $store = Store::factory()->create();
    $configuredAt = now()->subDays(5);

    $register = PosRegister::factory()->create([
        'device_id' => 'test-device',
        'store_id' => $store->id,
        'configured_at' => $configuredAt,
    ]);

    $response = $this->withCookie(PosConfig::DEVICE_COOKIE_NAME, 'test-device')
        ->put(route('pos.register.update'), [
            'name' => 'Updated Name',
            'store_id' => $store->id,
            'moneybox_id' => null,
        ]);

    $response->assertRedirect(route('pos.index'));

    $register->refresh();
    // Configured_at should be preserved (not changed to a new timestamp)
    expect($register->configured_at)->not->toBeNull();

    // Parse as Carbon for comparison
    $updatedConfiguredAt = Illuminate\Support\Facades\Date::parse($register->configured_at);
    expect(abs($updatedConfiguredAt->timestamp - $configuredAt->timestamp))->toBeLessThan(2);
});
