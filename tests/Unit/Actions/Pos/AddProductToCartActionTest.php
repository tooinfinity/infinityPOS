<?php

declare(strict_types=1);

use App\Actions\Pos\AddProductToCart;
use App\Data\Pos\AddProductToCartData;
use App\Models\PosRegister;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;
use App\Services\Pos\CartService;
use App\Services\Pos\PosConfig;
use App\Services\Pos\RegisterContext;
use App\Settings\InventorySettings;
use Illuminate\Http\Request;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->store = Store::factory()->create();
});

test('it adds product to cart', function (): void {
    $product = Product::factory()->create(['price' => 1000, 'cost' => 500, 'is_active' => true]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device');

    PosRegister::factory()->create([
        'device_id' => 'test-device',
        'store_id' => $this->store->id,
    ]);

    $cartService = new CartService($request);
    $registerContext = new RegisterContext($request);
    $settings = resolve(InventorySettings::class);
    $settings->auto_deduct_stock = false;
    $settings->save();

    $action = new AddProductToCart($cartService, $registerContext, $settings);

    $data = new AddProductToCartData(
        product_id: $product->id,
        quantity: 2
    );

    $action->handle($data, $this->user->id);

    $sale = $cartService->getDraftSale();
    expect($sale)->toBeInstanceOf(Sale::class);

    $items = $sale->items;
    expect($items)->toHaveCount(1);
    expect($items->first()->product_id)->toBe($product->id);
    expect($items->first()->quantity)->toBe(2);
    expect($items->first()->price)->toBe(1000);
});

test('it increments quantity for existing product', function (): void {
    $product = Product::factory()->create(['is_active' => true]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device-2');

    PosRegister::factory()->create([
        'device_id' => 'test-device-2',
        'store_id' => $this->store->id,
    ]);

    $cartService = new CartService($request);
    $registerContext = new RegisterContext($request);
    $settings = resolve(InventorySettings::class);
    $settings->auto_deduct_stock = false;
    $settings->save();

    $action = new AddProductToCart($cartService, $registerContext, $settings);

    $data = new AddProductToCartData(product_id: $product->id, quantity: 1);
    $action->handle($data, $this->user->id);
    $action->handle($data, $this->user->id);

    $sale = $cartService->getDraftSale();
    expect($sale->items)->toHaveCount(1);
    expect($sale->items->first()->quantity)->toBe(2);
});

test('it throws exception for inactive product', function (): void {
    $product = Product::factory()->create(['is_active' => false]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device-3');

    $cartService = new CartService($request);
    $registerContext = new RegisterContext($request);
    $settings = resolve(InventorySettings::class);

    $action = new AddProductToCart($cartService, $registerContext, $settings);

    $data = new AddProductToCartData(product_id: $product->id, quantity: 1);
    $action->handle($data, $this->user->id);
})->throws(Illuminate\Database\Eloquent\ModelNotFoundException::class);

test('it throws exception for non-existent product', function (): void {
    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device-4');

    $cartService = new CartService($request);
    $registerContext = new RegisterContext($request);
    $settings = resolve(InventorySettings::class);

    $action = new AddProductToCart($cartService, $registerContext, $settings);

    $data = new AddProductToCartData(product_id: 99999, quantity: 1);
    $action->handle($data, $this->user->id);
})->throws(Illuminate\Database\Eloquent\ModelNotFoundException::class);
