<?php

declare(strict_types=1);

use App\Actions\Pos\ApplyDiscount;
use App\Data\Pos\ApplyCartDiscountData;
use App\Models\PosRegister;
use App\Models\Store;
use App\Models\User;
use App\Services\Pos\CartService;
use App\Services\Pos\PosConfig;
use Illuminate\Http\Request;

test('it applies discount to cart', function (): void {
    $user = User::factory()->create();
    $store = Store::factory()->create();

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device');

    PosRegister::factory()->create([
        'device_id' => 'test-device',
        'store_id' => $store->id,
    ]);

    $cartService = new CartService($request);
    $action = new ApplyDiscount($cartService);

    $data = new ApplyCartDiscountData(discount: 1500);
    $action->handle($data, $user->id);

    $sale = $cartService->getDraftSale();
    expect($sale->discount)->toBe(1500);
});

test('it updates existing discount', function (): void {
    $user = User::factory()->create();
    $store = Store::factory()->create();

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device-2');

    PosRegister::factory()->create([
        'device_id' => 'test-device-2',
        'store_id' => $store->id,
    ]);

    $cartService = new CartService($request);
    $action = new ApplyDiscount($cartService);

    $action->handle(new ApplyCartDiscountData(discount: 1000), $user->id);
    $action->handle(new ApplyCartDiscountData(discount: 2000), $user->id);

    $sale = $cartService->getDraftSale();
    expect($sale->discount)->toBe(2000);
});
