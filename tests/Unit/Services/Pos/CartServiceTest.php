<?php

declare(strict_types=1);

use App\Enums\SaleStatusEnum;
use App\Models\PosRegister;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\User;
use App\Services\Pos\CartService;
use App\Services\Pos\PosConfig;
use Illuminate\Http\Request;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->store = Store::factory()->create();
});

test('it returns null draft sale id when no register exists', function (): void {
    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'new-device');

    $service = new CartService($request);
    $draftId = $service->getDraftSaleId();

    expect($draftId)->toBeNull();
});

test('it returns null draft sale when no sale exists', function (): void {
    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'new-device');

    $service = new CartService($request);
    $draft = $service->getDraftSale();

    expect($draft)->toBeNull();
});

test('it creates a draft sale', function (): void {
    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device');

    PosRegister::factory()->create([
        'device_id' => 'test-device',
        'store_id' => $this->store->id,
    ]);

    $service = new CartService($request);
    $draft = $service->getOrCreateDraftSale($this->user->id);

    expect($draft)
        ->toBeInstanceOf(Sale::class)
        ->status->toBe(SaleStatusEnum::PENDING)
        ->store_id->toBe($this->store->id)
        ->created_by->toBe($this->user->id)
        ->subtotal->toBe(0)
        ->total->toBe(0);
});

test('it returns existing draft sale', function (): void {
    $register = PosRegister::factory()->create([
        'device_id' => 'test-device',
        'store_id' => $this->store->id,
    ]);

    $sale = Sale::factory()->create([
        'store_id' => $this->store->id,
        'status' => SaleStatusEnum::PENDING,
        'created_by' => $this->user->id,
    ]);

    $register->update(['draft_sale_id' => $sale->id]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device');

    $service = new CartService($request);
    $draft = $service->getOrCreateDraftSale($this->user->id);

    expect($draft->id)->toBe($sale->id);
});

test('it returns empty cart when no draft sale', function (): void {
    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'new-device');

    $service = new CartService($request);
    $raw = $service->getRaw();

    expect($raw)->toBe([
        'items' => [],
        'discount' => 0,
        'sale_id' => null,
    ]);
});

test('it returns cart with items', function (): void {
    $product = Product::factory()->create(['name' => 'Test Product']);

    $register = PosRegister::factory()->create([
        'device_id' => 'test-device',
        'store_id' => $this->store->id,
    ]);

    $sale = Sale::factory()->create([
        'store_id' => $this->store->id,
        'status' => SaleStatusEnum::PENDING,
        'discount' => 1000,
    ]);

    $saleItem = SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 5000,
    ]);

    $register->update(['draft_sale_id' => $sale->id]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device');

    $service = new CartService($request);
    $raw = $service->getRaw();

    expect($raw)
        ->toHaveKey('items')
        ->toHaveKey('discount')
        ->toHaveKey('sale_id')
        ->and($raw['discount'])->toBe(1000)
        ->and($raw['sale_id'])->toBe($sale->id)
        ->and($raw['items'])->toHaveCount(1);

    $itemKey = 'item_'.$saleItem->id;
    expect($raw['items'][$itemKey])
        ->product_id->toBe($product->id)
        ->name->toBe('Test Product')
        ->unit_price->toBe(5000)
        ->quantity->toBe(2);
});

test('it sets discount on draft sale', function (): void {
    $register = PosRegister::factory()->create([
        'device_id' => 'test-device',
        'store_id' => $this->store->id,
    ]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device');

    $service = new CartService($request);
    $draft = $service->getOrCreateDraftSale($this->user->id);

    $service->setDiscount($this->user->id, 1500);

    $draft->refresh();
    expect($draft->discount)->toBe(1500);
});

test('it prevents negative discount', function (): void {
    $register = PosRegister::factory()->create([
        'device_id' => 'test-device',
        'store_id' => $this->store->id,
    ]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device');

    $service = new CartService($request);
    $draft = $service->getOrCreateDraftSale($this->user->id);

    $service->setDiscount($this->user->id, -500);

    $draft->refresh();
    expect($draft->discount)->toBe(0);
});

test('it clears cart and deletes draft sale', function (): void {
    $product = Product::factory()->create();

    $register = PosRegister::factory()->create([
        'device_id' => 'test-device',
        'store_id' => $this->store->id,
    ]);

    $sale = Sale::factory()->create([
        'store_id' => $this->store->id,
        'status' => SaleStatusEnum::PENDING,
    ]);

    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    $register->update(['draft_sale_id' => $sale->id]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device');

    $service = new CartService($request);
    $service->clear($this->user->id);

    expect(Sale::query()->find($sale->id))->toBeNull()
        ->and(SaleItem::query()->where('sale_id', $sale->id)->count())->toBe(0);

    $register->refresh();
    expect($register->draft_sale_id)->toBeNull();
});

test('it handles clear when no draft sale exists', function (): void {
    $register = PosRegister::factory()->create([
        'device_id' => 'test-device',
        'store_id' => $this->store->id,
        'draft_sale_id' => null,
    ]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device');

    $service = new CartService($request);

    // Should not throw exception
    $service->clear($this->user->id);

    $register->refresh();
    expect($register->draft_sale_id)->toBeNull();
});

test('it creates register if not exists', function (): void {
    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'new-unique-device');

    $service = new CartService($request);
    $draft = $service->getOrCreateDraftSale($this->user->id);

    $register = PosRegister::query()->where('device_id', 'new-unique-device')->first();

    expect($register)
        ->toBeInstanceOf(PosRegister::class)
        ->device_id->toBe('new-unique-device');
});

test('it creates default store if none exists', function (): void {
    Store::query()->delete();

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'new-device-no-store');

    $service = new CartService($request);
    $draft = $service->getOrCreateDraftSale($this->user->id);

    $store = Store::query()->first();
    expect($store)
        ->toBeInstanceOf(Store::class)
        ->name->toBe('Default Store')
        ->is_active->toBeTrue();
});

test('it uses active store when available', function (): void {
    Store::query()->delete();

    $inactiveStore = Store::factory()->create(['is_active' => false]);
    $activeStore = Store::factory()->create(['is_active' => true]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'device-with-active-store');

    $service = new CartService($request);
    $draft = $service->getOrCreateDraftSale($this->user->id);

    $register = PosRegister::query()->where('device_id', 'device-with-active-store')->first();
    expect($register->store_id)->toBe($activeStore->id);
});
