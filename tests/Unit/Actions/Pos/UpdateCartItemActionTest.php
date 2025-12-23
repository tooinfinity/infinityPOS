<?php

declare(strict_types=1);

use App\Actions\Pos\UpdateCartItem;
use App\Data\Pos\UpdateCartItemData;
use App\Enums\SaleStatusEnum;
use App\Models\PosRegister;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Services\Pos\CartService;
use App\Services\Pos\PosConfig;
use Illuminate\Http\Request;

test('it updates cart item quantity', function (): void {
    $store = Store::factory()->create();
    $product = Product::factory()->create();

    $register = PosRegister::factory()->create([
        'device_id' => 'test-device',
        'store_id' => $store->id,
    ]);

    $sale = Sale::factory()->create([
        'store_id' => $store->id,
        'status' => SaleStatusEnum::PENDING,
    ]);

    $saleItem = SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 1000,
    ]);

    $register->update(['draft_sale_id' => $sale->id]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device');

    $cartService = new CartService($request);
    $action = new UpdateCartItem($cartService);

    $data = new UpdateCartItemData(quantity: 5);
    $action->handle('item_'.$saleItem->id, $data);

    $saleItem->refresh();
    expect($saleItem->quantity)->toBe(5);
});

test('it deletes item when quantity is zero or negative', function (): void {
    $store = Store::factory()->create();
    $product = Product::factory()->create();

    $register = PosRegister::factory()->create([
        'device_id' => 'test-device-2',
        'store_id' => $store->id,
    ]);

    $sale = Sale::factory()->create([
        'store_id' => $store->id,
        'status' => SaleStatusEnum::PENDING,
    ]);

    $saleItem = SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $register->update(['draft_sale_id' => $sale->id]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device-2');

    $cartService = new CartService($request);
    $action = new UpdateCartItem($cartService);

    $data = new UpdateCartItemData(quantity: 0);
    $action->handle('item_'.$saleItem->id, $data);

    expect(SaleItem::query()->find($saleItem->id))->toBeNull();
});

test('it throws exception when cart is empty', function (): void {
    $store = Store::factory()->create();

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device-3');

    PosRegister::factory()->create([
        'device_id' => 'test-device-3',
        'store_id' => $store->id,
    ]);

    $cartService = new CartService($request);
    $action = new UpdateCartItem($cartService);

    $data = new UpdateCartItemData(quantity: 5);
    $action->handle('item_1', $data);
})->throws(DomainException::class, 'Cart is empty');

test('it throws exception for invalid line id', function (): void {
    $store = Store::factory()->create();

    $register = PosRegister::factory()->create([
        'device_id' => 'test-device-4',
        'store_id' => $store->id,
    ]);

    $sale = Sale::factory()->create([
        'store_id' => $store->id,
        'status' => SaleStatusEnum::PENDING,
    ]);

    $register->update(['draft_sale_id' => $sale->id]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device-4');

    $cartService = new CartService($request);
    $action = new UpdateCartItem($cartService);

    $data = new UpdateCartItemData(quantity: 5);
    $action->handle('invalid_format', $data);
})->throws(DomainException::class, 'Cart line not found');

test('it throws exception for non-existent item', function (): void {
    $store = Store::factory()->create();

    $register = PosRegister::factory()->create([
        'device_id' => 'test-device-5',
        'store_id' => $store->id,
    ]);

    $sale = Sale::factory()->create([
        'store_id' => $store->id,
        'status' => SaleStatusEnum::PENDING,
    ]);

    $register->update(['draft_sale_id' => $sale->id]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device-5');

    $cartService = new CartService($request);
    $action = new UpdateCartItem($cartService);

    $data = new UpdateCartItemData(quantity: 5);
    $action->handle('item_99999', $data);
})->throws(DomainException::class, 'Cart line not found');
