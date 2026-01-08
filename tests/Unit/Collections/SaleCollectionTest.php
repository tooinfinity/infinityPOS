<?php

declare(strict_types=1);

use App\Collections\SaleCollection;
use App\Enums\PaymentMethodEnum;
use App\Enums\SaleStatusEnum;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\User;

test('total profit returns sum of all sale item profits', function (): void {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $sale1 = Sale::factory()->create([
        'store_id' => $store->id,
        'cashier_id' => $user->id,
        'total_amount' => 10000,
    ]);

    SaleItem::factory()->create([
        'sale_id' => $sale1->id,
        'profit' => 1000,
    ]);

    SaleItem::factory()->create([
        'sale_id' => $sale1->id,
        'profit' => 500,
    ]);

    $sale2 = Sale::factory()->create([
        'store_id' => $store->id,
        'cashier_id' => $user->id,
        'total_amount' => 20000,
    ]);

    SaleItem::factory()->create([
        'sale_id' => $sale2->id,
        'profit' => 2500,
    ]);

    $collection = new SaleCollection([$sale1, $sale2]);

    expect($collection->totalProfit())->toBe(4000);
});

test('total profit returns zero for empty collection', function (): void {
    $collection = new SaleCollection([]);

    expect($collection->totalProfit())->toBe(0);
});

test('total profit returns zero when sales have no items', function (): void {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $sale = Sale::factory()->create([
        'store_id' => $store->id,
        'cashier_id' => $user->id,
        'total_amount' => 10000,
    ]);

    $collection = new SaleCollection([$sale]);

    expect($collection->totalProfit())->toBe(0);
});

test('total revenue returns sum of all sale amounts', function (): void {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $sale1 = Sale::factory()->create([
        'store_id' => $store->id,
        'cashier_id' => $user->id,
        'total_amount' => 10000,
    ]);

    $sale2 = Sale::factory()->create([
        'store_id' => $store->id,
        'cashier_id' => $user->id,
        'total_amount' => 25000,
    ]);

    $sale3 = Sale::factory()->create([
        'store_id' => $store->id,
        'cashier_id' => $user->id,
        'total_amount' => 15000,
    ]);

    $collection = new SaleCollection([$sale1, $sale2, $sale3]);

    expect($collection->totalRevenue())->toBe(50000);
});

test('total revenue returns zero for empty collection', function (): void {
    $collection = new SaleCollection([]);

    expect($collection->totalRevenue())->toBe(0);
});

test('by payment method groups sales by payment method', function (): void {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $sale1 = Sale::factory()->create([
        'store_id' => $store->id,
        'cashier_id' => $user->id,
        'payment_method' => PaymentMethodEnum::CASH,
        'total_amount' => 10000,
    ]);

    $sale2 = Sale::factory()->create([
        'store_id' => $store->id,
        'cashier_id' => $user->id,
        'payment_method' => PaymentMethodEnum::CASH,
        'total_amount' => 15000,
    ]);

    $sale3 = Sale::factory()->create([
        'store_id' => $store->id,
        'cashier_id' => $user->id,
        'payment_method' => PaymentMethodEnum::CARD,
        'total_amount' => 20000,
    ]);

    $collection = new SaleCollection([$sale1, $sale2, $sale3]);

    $result = $collection->byPaymentMethod();

    expect($result)
        ->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->toHaveCount(2)
        ->and($result->get('cash'))
        ->toBe(['count' => 2, 'total' => 25000])
        ->and($result->get('card'))
        ->toBe(['count' => 1, 'total' => 20000]);
});

test('by payment method returns empty collection for empty sales', function (): void {
    $collection = new SaleCollection([]);

    $result = $collection->byPaymentMethod();

    expect($result)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->toHaveCount(0);
});

test('by payment method handles single payment method', function (): void {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $sale1 = Sale::factory()->create([
        'store_id' => $store->id,
        'cashier_id' => $user->id,
        'payment_method' => PaymentMethodEnum::CASH,
        'total_amount' => 10000,
    ]);

    $sale2 = Sale::factory()->create([
        'store_id' => $store->id,
        'cashier_id' => $user->id,
        'payment_method' => PaymentMethodEnum::CASH,
        'total_amount' => 5000,
    ]);

    $collection = new SaleCollection([$sale1, $sale2]);

    $result = $collection->byPaymentMethod();

    expect($result)->toHaveCount(1)
        ->and($result->get('cash'))
        ->toBe(['count' => 2, 'total' => 15000]);
});

test('completed returns only completed sales', function (): void {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $completedSale1 = Sale::factory()->create([
        'store_id' => $store->id,
        'cashier_id' => $user->id,
        'status' => SaleStatusEnum::COMPLETED,
    ]);

    $completedSale2 = Sale::factory()->create([
        'store_id' => $store->id,
        'cashier_id' => $user->id,
        'status' => SaleStatusEnum::COMPLETED,
    ]);

    $pendingSale = Sale::factory()->create([
        'store_id' => $store->id,
        'cashier_id' => $user->id,
        'status' => SaleStatusEnum::PENDING,
    ]);

    $returnedSale = Sale::factory()->create([
        'store_id' => $store->id,
        'cashier_id' => $user->id,
        'status' => SaleStatusEnum::RETURNED,
    ]);

    $collection = new SaleCollection([
        $completedSale1,
        $pendingSale,
        $completedSale2,
        $returnedSale,
    ]);

    $result = $collection->completed();

    expect($result)
        ->toBeInstanceOf(SaleCollection::class)
        ->toHaveCount(2)
        ->and($result->pluck('id')->toArray())
        ->toBe([$completedSale1->id, $completedSale2->id]);
});

test('completed returns empty collection when no completed sales', function (): void {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $pendingSale = Sale::factory()->create([
        'store_id' => $store->id,
        'cashier_id' => $user->id,
        'status' => SaleStatusEnum::PENDING,
    ]);

    $collection = new SaleCollection([$pendingSale]);

    expect($collection->completed())->toHaveCount(0);
});

test('average sale amount returns correct average', function (): void {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $sale1 = Sale::factory()->create([
        'store_id' => $store->id,
        'cashier_id' => $user->id,
        'total_amount' => 10000,
    ]);

    $sale2 = Sale::factory()->create([
        'store_id' => $store->id,
        'cashier_id' => $user->id,
        'total_amount' => 20000,
    ]);

    $sale3 = Sale::factory()->create([
        'store_id' => $store->id,
        'cashier_id' => $user->id,
        'total_amount' => 30000,
    ]);

    $collection = new SaleCollection([$sale1, $sale2, $sale3]);

    expect($collection->averageSaleAmount())->toBe(20000.0);
});

test('average sale amount returns zero for empty collection', function (): void {
    $collection = new SaleCollection([]);

    expect($collection->averageSaleAmount())->toBe(0.0);
});

test('average sale amount returns correct value for single sale', function (): void {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $sale = Sale::factory()->create([
        'store_id' => $store->id,
        'cashier_id' => $user->id,
        'total_amount' => 15000,
    ]);

    $collection = new SaleCollection([$sale]);

    expect($collection->averageSaleAmount())->toBe(15000.0);
});

test('average sale amount handles decimal division', function (): void {
    $store = Store::factory()->create();
    $user = User::factory()->create();

    $sale1 = Sale::factory()->create([
        'store_id' => $store->id,
        'cashier_id' => $user->id,
        'total_amount' => 10000,
    ]);

    $sale2 = Sale::factory()->create([
        'store_id' => $store->id,
        'cashier_id' => $user->id,
        'total_amount' => 15000,
    ]);

    $collection = new SaleCollection([$sale1, $sale2]);

    expect($collection->averageSaleAmount())->toBe(12500.0);
});
