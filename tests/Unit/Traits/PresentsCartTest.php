<?php

declare(strict_types=1);

use App\Actions\Pos\CalculateCartTotals;
use App\Data\Pos\PosCartData;
use App\Data\Pos\PosCartItemData;
use App\Enums\SaleStatusEnum;
use App\Models\PosRegister;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\User;
use App\Services\Pos\CartService;
use App\Services\Pos\PosConfig;
use App\Traits\PresentsCart;
use Illuminate\Http\Request;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->store = Store::factory()->create();
});

test('it presents cart data correctly', function (): void {
    $this->actingAs($this->user);

    $product1 = Product::factory()->create(['name' => 'Product A']);
    $product2 = Product::factory()->create(['name' => 'Product B']);

    $register = PosRegister::factory()->create([
        'device_id' => 'test-device',
        'store_id' => $this->store->id,
    ]);

    $sale = Sale::factory()->create([
        'store_id' => $this->store->id,
        'status' => SaleStatusEnum::PENDING,
        'discount' => 500,
    ]);

    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product1->id,
        'quantity' => 2,
        'price' => 1000,
    ]);

    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product2->id,
        'quantity' => 1,
        'price' => 1500,
    ]);

    $register->update(['draft_sale_id' => $sale->id]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device');

    $cartService = new CartService($request);
    $calculateTotals = new CalculateCartTotals();

    $testClass = new class
    {
        use PresentsCart;

        public function testPresent(CartService $cart, CalculateCartTotals $totals): PosCartData
        {
            return $this->present($cart, $totals);
        }
    };

    $result = $testClass->testPresent($cartService, $calculateTotals);

    expect($result)
        ->toBeInstanceOf(PosCartData::class)
        ->items->toHaveCount(2)
        ->and($result->items[0])
        ->toBeInstanceOf(PosCartItemData::class)
        ->product_id->toBeIn([$product1->id, $product2->id])
        ->name->toBeIn(['Product A', 'Product B'])
        ->unit_price->toBeIn([1000, 1500])
        ->quantity->toBeIn([1, 2]);

});

test('it presents empty cart', function (): void {
    $this->actingAs($this->user);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'empty-cart-device');

    $cartService = new CartService($request);
    $calculateTotals = new CalculateCartTotals();

    $testClass = new class
    {
        use PresentsCart;

        public function testPresent(CartService $cart, CalculateCartTotals $totals): PosCartData
        {
            return $this->present($cart, $totals);
        }
    };

    $result = $testClass->testPresent($cartService, $calculateTotals);

    expect($result)
        ->toBeInstanceOf(PosCartData::class)
        ->items->toBeEmpty();
});

test('it aborts when user is not authenticated', function (): void {
    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device');

    $cartService = new CartService($request);
    $calculateTotals = new CalculateCartTotals();

    $testClass = new class
    {
        use PresentsCart;

        public function testPresent(CartService $cart, CalculateCartTotals $totals): PosCartData
        {
            return $this->present($cart, $totals);
        }
    };

    $testClass->testPresent($cartService, $calculateTotals);
})->throws(Symfony\Component\HttpKernel\Exception\HttpException::class);

test('it calculates line subtotal correctly', function (): void {
    $this->actingAs($this->user);

    $product = Product::factory()->create(['name' => 'Product']);

    $register = PosRegister::factory()->create([
        'device_id' => 'test-device-calc',
        'store_id' => $this->store->id,
    ]);

    $sale = Sale::factory()->create([
        'store_id' => $this->store->id,
        'status' => SaleStatusEnum::PENDING,
    ]);

    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 3,
        'price' => 2500,
    ]);

    $register->update(['draft_sale_id' => $sale->id]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device-calc');

    $cartService = new CartService($request);
    $calculateTotals = new CalculateCartTotals();

    $testClass = new class
    {
        use PresentsCart;

        public function testPresent(CartService $cart, CalculateCartTotals $totals): PosCartData
        {
            return $this->present($cart, $totals);
        }
    };

    $result = $testClass->testPresent($cartService, $calculateTotals);

    expect($result->items[0]->line_subtotal)->toBe(7500);
});
