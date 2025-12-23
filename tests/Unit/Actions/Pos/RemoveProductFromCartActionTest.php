<?php

declare(strict_types=1);

use App\Actions\Pos\RemoveProductFromCart;
use App\Enums\SaleStatusEnum;
use App\Models\PosRegister;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Services\Pos\CartService;
use App\Services\Pos\PosConfig;
use Illuminate\Http\Request;

test('it removes product from cart', function (): void {
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
    ]);

    $register->update(['draft_sale_id' => $sale->id]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device');

    $cartService = new CartService($request);
    $action = new RemoveProductFromCart($cartService);

    $action->handle('item_'.$saleItem->id);

    expect(SaleItem::query()->find($saleItem->id))->toBeNull();
});

test('it handles removing non-existent item', function (): void {
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

    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    $register->update(['draft_sale_id' => $sale->id]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device-2');

    $cartService = new CartService($request);
    $action = new RemoveProductFromCart($cartService);

    // Try to remove item that doesn't exist - should not throw exception
    // This covers line 21 by having a sale, valid format, but non-existent item
    $action->handle('item_99999');

    // Verify original item still exists
    expect(SaleItem::query()->where('sale_id', $sale->id)->count())->toBe(1);
});

test('it ignores invalid line id format', function (): void {
    $store = Store::factory()->create();
    $product = Product::factory()->create();

    $register = PosRegister::factory()->create([
        'device_id' => 'test-device-3',
        'store_id' => $store->id,
    ]);

    $sale = Sale::factory()->create([
        'store_id' => $store->id,
        'status' => SaleStatusEnum::PENDING,
    ]);

    $saleItem = SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    $register->update(['draft_sale_id' => $sale->id]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device-3');

    $cartService = new CartService($request);
    $action = new RemoveProductFromCart($cartService);

    // Should not throw exception - returns early due to invalid format
    $action->handle('invalid_format');

    // Verify item still exists (wasn't removed)
    expect(SaleItem::query()->find($saleItem->id))->not->toBeNull();
});

test('it handles empty cart', function (): void {
    $store = Store::factory()->create();

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device-4');

    PosRegister::factory()->create([
        'device_id' => 'test-device-4',
        'store_id' => $store->id,
    ]);

    $cartService = new CartService($request);
    $action = new RemoveProductFromCart($cartService);

    // Should not throw exception when cart is empty
    $action->handle('item_1');

    expect(true)->toBeTrue();
});
