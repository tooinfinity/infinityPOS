<?php

declare(strict_types=1);

use App\Actions\Pos\ProcessPosPayment;
use App\Data\Pos\ProcessPosPaymentData;
use App\Enums\MoneyboxTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\SaleStatusEnum;
use App\Enums\TaxTypeEnum;
use App\Models\Moneybox;
use App\Models\MoneyboxTransaction;
use App\Models\PosRegister;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\Tax;
use App\Models\User;
use App\Services\Pos\PosConfig;
use App\Settings\InventorySettings;
use App\Settings\PosSettings;
use Illuminate\Http\Request;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->store = Store::factory()->create();
});

test('it processes payment with multiple items and proportional discount', function (): void {
    // This test covers line 85: min($discount, $remainingDiscount)
    $product1 = Product::factory()->create(['price' => 1000, 'cost' => 500]);
    $product2 = Product::factory()->create(['price' => 2000, 'cost' => 1000]);
    $product3 = Product::factory()->create(['price' => 500, 'cost' => 250]);

    $register = PosRegister::factory()->create([
        'device_id' => 'test-device',
        'store_id' => $this->store->id,
        'configured_at' => now(),
    ]);

    // Bind the request to the container with the cookie
    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device');

    app()->instance('request', $request);

    $cartService = new App\Services\Pos\CartService($request);

    // Create draft sale with items
    $sale = $cartService->getOrCreateDraftSale($this->user->id);
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
        'price' => 2000,
    ]);
    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product3->id,
        'quantity' => 1,
        'price' => 500,
    ]);

    // Set a discount that will trigger proportional allocation
    $cartService->setDiscount($this->user->id, 1000);

    // Disable stock validation for this test
    $inventorySettings = resolve(InventorySettings::class);
    $inventorySettings->auto_deduct_stock = false;
    $inventorySettings->save();

    $action = resolve(ProcessPosPayment::class);

    $data = new ProcessPosPaymentData(
        store_id: $this->store->id,
        amount: 3500,
        method: PaymentMethodEnum::CASH,
        client_id: null,
        reference: null,
        notes: null
    );

    $completedSale = $action->handle($data, $this->user->id);

    expect($completedSale)->toBeInstanceOf(Sale::class);
    expect($completedSale->status)->toBe(SaleStatusEnum::COMPLETED);
    expect($completedSale->items)->toHaveCount(3);

    // Verify discount was allocated proportionally
    $totalDiscount = $completedSale->items->sum('discount');
    expect($totalDiscount)->toBe(1000);
});

test('it processes payment with fixed tax type', function (): void {
    // This test covers line 98: FIXED tax type in match expression
    $tax = Tax::factory()->create([
        'tax_type' => TaxTypeEnum::FIXED,
        'rate' => 50,
        'is_active' => true,
    ]);

    $product = Product::factory()->create([
        'price' => 1000,
        'cost' => 500,
        'tax_id' => $tax->id,
    ]);

    $register = PosRegister::factory()->create([
        'device_id' => 'test-device-fixed-tax',
        'store_id' => $this->store->id,
        'configured_at' => now(),
    ]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device-fixed-tax');

    app()->instance('request', $request);

    $cartService = new App\Services\Pos\CartService($request);

    // Create draft sale with item
    $sale = $cartService->getOrCreateDraftSale($this->user->id);
    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 3,
        'price' => 1000,
    ]);

    // Disable stock validation
    $inventorySettings = resolve(InventorySettings::class);
    $inventorySettings->auto_deduct_stock = false;
    $inventorySettings->save();

    $action = resolve(ProcessPosPayment::class);

    $data = new ProcessPosPaymentData(
        store_id: $this->store->id,
        amount: 3150,
        method: PaymentMethodEnum::CASH,
        client_id: null,
        reference: null,
        notes: null
    );

    $completedSale = $action->handle($data, $this->user->id);

    expect($completedSale)->toBeInstanceOf(Sale::class);
    expect($completedSale->status)->toBe(SaleStatusEnum::COMPLETED);

    // Verify fixed tax was applied: 50 * 3 = 150
    $totalTax = $completedSale->items->sum('tax_amount');
    expect($totalTax)->toBe(150);
});

test('it processes payment with fixed tax and discount', function (): void {
    // This covers both line 85 and 98 together
    $tax = Tax::factory()->create([
        'tax_type' => TaxTypeEnum::FIXED,
        'rate' => 25,
        'is_active' => true,
    ]);

    $product1 = Product::factory()->create([
        'price' => 1000,
        'cost' => 500,
        'tax_id' => $tax->id,
    ]);
    $product2 = Product::factory()->create([
        'price' => 1500,
        'cost' => 750,
        'tax_id' => $tax->id,
    ]);

    $register = PosRegister::factory()->create([
        'device_id' => 'test-device-fixed-discount',
        'store_id' => $this->store->id,
        'configured_at' => now(),
    ]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device-fixed-discount');

    app()->instance('request', $request);

    $cartService = new App\Services\Pos\CartService($request);

    // Create draft sale with items
    $sale = $cartService->getOrCreateDraftSale($this->user->id);
    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product1->id,
        'quantity' => 2,
        'price' => 1000,
    ]);
    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product2->id,
        'quantity' => 2,
        'price' => 1500,
    ]);

    // Set discount
    $cartService->setDiscount($this->user->id, 500);

    // Disable stock validation
    $inventorySettings = resolve(InventorySettings::class);
    $inventorySettings->auto_deduct_stock = false;
    $inventorySettings->save();

    $action = resolve(ProcessPosPayment::class);

    $data = new ProcessPosPaymentData(
        store_id: $this->store->id,
        amount: 4600,
        method: PaymentMethodEnum::CASH,
        client_id: null,
        reference: null,
        notes: null
    );

    $completedSale = $action->handle($data, $this->user->id);

    expect($completedSale)->toBeInstanceOf(Sale::class);
    expect($completedSale->status)->toBe(SaleStatusEnum::COMPLETED);

    // Verify fixed tax: (25 * 2) + (25 * 2) = 100
    $totalTax = $completedSale->items->sum('tax_amount');
    expect($totalTax)->toBe(100);

    // Verify discount was applied
    $totalDiscount = $completedSale->items->sum('discount');
    expect($totalDiscount)->toBe(500);
});

test('it records moneybox transaction for cash payment with drawer', function (): void {
    $moneybox = Moneybox::factory()->create([
        'type' => MoneyboxTypeEnum::CASH_REGISTER,
        'store_id' => $this->store->id,
    ]);

    $product = Product::factory()->create(['price' => 1000, 'cost' => 500]);

    $register = PosRegister::factory()->create([
        'device_id' => 'test-device-moneybox',
        'store_id' => $this->store->id,
        'moneybox_id' => $moneybox->id,
        'configured_at' => now(),
    ]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device-moneybox');

    app()->instance('request', $request);

    $cartService = new App\Services\Pos\CartService($request);

    // Create draft sale
    $sale = $cartService->getOrCreateDraftSale($this->user->id);
    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 1000,
    ]);

    // Disable stock validation
    $inventorySettings = resolve(InventorySettings::class);
    $inventorySettings->auto_deduct_stock = false;
    $inventorySettings->save();

    $action = resolve(ProcessPosPayment::class);

    $data = new ProcessPosPaymentData(
        store_id: $this->store->id,
        amount: 1000,
        method: PaymentMethodEnum::CASH,
        client_id: null,
        reference: 'REF-123',
        notes: 'Test payment'
    );

    $completedSale = $action->handle($data, $this->user->id);

    expect($completedSale)->toBeInstanceOf(Sale::class);

    // Verify moneybox transaction was created
    $transaction = MoneyboxTransaction::query()->where('moneybox_id', $moneybox->id)->first();
    expect($transaction)->not->toBeNull();
    expect($transaction->amount)->toBe(1000);
    expect($transaction->reference)->toBe('REF-123');
});

test('it does not record moneybox transaction for non-cash payment', function (): void {
    $moneybox = Moneybox::factory()->create([
        'type' => MoneyboxTypeEnum::CASH_REGISTER,
        'store_id' => $this->store->id,
    ]);

    $product = Product::factory()->create(['price' => 1000, 'cost' => 500]);

    $register = PosRegister::factory()->create([
        'device_id' => 'test-device-card',
        'store_id' => $this->store->id,
        'moneybox_id' => $moneybox->id,
        'configured_at' => now(),
    ]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device-card');

    app()->instance('request', $request);

    $cartService = new App\Services\Pos\CartService($request);

    // Create draft sale
    $sale = $cartService->getOrCreateDraftSale($this->user->id);
    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 1000,
    ]);

    // Disable stock validation
    $inventorySettings = resolve(InventorySettings::class);
    $inventorySettings->auto_deduct_stock = false;
    $inventorySettings->save();

    $action = resolve(ProcessPosPayment::class);

    $data = new ProcessPosPaymentData(
        store_id: $this->store->id,
        amount: 1000,
        method: PaymentMethodEnum::CARD,
        client_id: null,
        reference: null,
        notes: null
    );

    $completedSale = $action->handle($data, $this->user->id);

    expect($completedSale)->toBeInstanceOf(Sale::class);

    // Verify NO moneybox transaction was created for card payment
    $transaction = MoneyboxTransaction::query()->where('moneybox_id', $moneybox->id)->first();
    expect($transaction)->toBeNull();
});

test('it throws exception when cash drawer required but not configured', function (): void {
    $product = Product::factory()->create(['price' => 1000, 'cost' => 500]);

    $register = PosRegister::factory()->create([
        'device_id' => 'test-device-no-drawer',
        'store_id' => $this->store->id,
        'moneybox_id' => null, // No cash drawer
        'configured_at' => now(),
    ]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device-no-drawer');

    app()->instance('request', $request);

    $cartService = new App\Services\Pos\CartService($request);

    // Create draft sale
    $sale = $cartService->getOrCreateDraftSale($this->user->id);
    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 1000,
    ]);

    // Enable cash drawer requirement
    $posSettings = resolve(PosSettings::class);
    $posSettings->require_cash_drawer_for_cash_payments = true;
    $posSettings->save();

    // Disable stock validation
    $inventorySettings = resolve(InventorySettings::class);
    $inventorySettings->auto_deduct_stock = false;
    $inventorySettings->save();

    $action = resolve(ProcessPosPayment::class);

    $data = new ProcessPosPaymentData(
        store_id: $this->store->id,
        amount: 1000,
        method: PaymentMethodEnum::CASH,
        client_id: null,
        reference: null,
        notes: null
    );

    $action->handle($data, $this->user->id);
})->throws(InvalidArgumentException::class, 'Cash drawer is required for cash payments');

test('it throws exception when cart is empty', function (): void {
    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device-empty');

    $register = PosRegister::factory()->create([
        'device_id' => 'test-device-empty',
        'store_id' => $this->store->id,
        'configured_at' => now(),
    ]);

    $action = resolve(ProcessPosPayment::class);

    $data = new ProcessPosPaymentData(
        store_id: $this->store->id,
        amount: 1000,
        method: PaymentMethodEnum::CASH,
        client_id: null,
        reference: null,
        notes: null
    );

    $action->handle($data, $this->user->id);
})->throws(InvalidArgumentException::class, 'Cart is empty');
