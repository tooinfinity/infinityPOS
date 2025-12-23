<?php

declare(strict_types=1);

use App\Actions\Pos\ClearCart;
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

test('it clears cart', function (): void {
    $user = User::factory()->create();
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

    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    $register->update(['draft_sale_id' => $sale->id]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device');

    $cartService = new CartService($request);
    $action = new ClearCart($cartService);

    $action->handle($user->id);

    expect(Sale::query()->find($sale->id))->toBeNull();
    expect(SaleItem::query()->where('sale_id', $sale->id)->count())->toBe(0);

    $register->refresh();
    expect($register->draft_sale_id)->toBeNull();
});

test('it handles clearing empty cart', function (): void {
    $user = User::factory()->create();
    $store = Store::factory()->create();

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device-empty');

    PosRegister::factory()->create([
        'device_id' => 'test-device-empty',
        'store_id' => $store->id,
    ]);

    $cartService = new CartService($request);
    $action = new ClearCart($cartService);

    // Should not throw exception
    $action->handle($user->id);

    expect($cartService->getDraftSale())->toBeNull();
});
