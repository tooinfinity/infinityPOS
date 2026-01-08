<?php

declare(strict_types=1);

use App\Collections\PurchaseCollection;
use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;

test('total cost returns sum of all purchase costs', function (): void {
    $store = Store::factory()->create();
    $supplier = Supplier::factory()->create();
    $user = User::factory()->create();

    $purchase1 = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'total_cost' => 10000,
    ]);

    $purchase2 = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'total_cost' => 25000,
    ]);

    $purchase3 = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'total_cost' => 15000,
    ]);

    $collection = new PurchaseCollection([$purchase1, $purchase2, $purchase3]);

    expect($collection->totalCost())->toBe(50000);
});

test('total cost returns zero for empty collection', function (): void {
    $collection = new PurchaseCollection([]);

    expect($collection->totalCost())->toBe(0);
});

test('total paid returns sum of all paid amounts', function (): void {
    $store = Store::factory()->create();
    $supplier = Supplier::factory()->create();
    $user = User::factory()->create();

    $purchase1 = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'total_cost' => 10000,
        'paid_amount' => 10000,
    ]);

    $purchase2 = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'total_cost' => 20000,
        'paid_amount' => 15000,
    ]);

    $purchase3 = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'total_cost' => 30000,
        'paid_amount' => 0,
    ]);

    $collection = new PurchaseCollection([$purchase1, $purchase2, $purchase3]);

    expect($collection->totalPaid())->toBe(25000);
});

test('total paid returns zero for empty collection', function (): void {
    $collection = new PurchaseCollection([]);

    expect($collection->totalPaid())->toBe(0);
});

test('total outstanding returns sum of all outstanding balances', function (): void {
    $store = Store::factory()->create();
    $supplier = Supplier::factory()->create();
    $user = User::factory()->create();

    $purchase1 = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'total_cost' => 10000,
        'paid_amount' => 10000, // Outstanding: 0
    ]);

    $purchase2 = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'total_cost' => 20000,
        'paid_amount' => 15000, // Outstanding: 5000
    ]);

    $purchase3 = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'total_cost' => 30000,
        'paid_amount' => 10000, // Outstanding: 20000
    ]);

    $collection = new PurchaseCollection([$purchase1, $purchase2, $purchase3]);

    expect($collection->totalOutstanding())->toBe(25000);
});

test('total outstanding returns zero for empty collection', function (): void {
    $collection = new PurchaseCollection([]);

    expect($collection->totalOutstanding())->toBe(0);
});

test('total outstanding returns zero when all purchases are fully paid', function (): void {
    $store = Store::factory()->create();
    $supplier = Supplier::factory()->create();
    $user = User::factory()->create();

    $purchase1 = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'total_cost' => 10000,
        'paid_amount' => 10000,
    ]);

    $purchase2 = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'total_cost' => 20000,
        'paid_amount' => 20000,
    ]);

    $collection = new PurchaseCollection([$purchase1, $purchase2]);

    expect($collection->totalOutstanding())->toBe(0);
});

test('by payment status filters purchases by status', function (): void {
    $store = Store::factory()->create();
    $supplier = Supplier::factory()->create();
    $user = User::factory()->create();

    $pendingPurchase1 = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'payment_status' => PurchaseStatusEnum::PENDING,
    ]);

    $pendingPurchase2 = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'payment_status' => PurchaseStatusEnum::PENDING,
    ]);

    $completedPurchase = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'payment_status' => PurchaseStatusEnum::COMPLETED,
    ]);

    $cancelledPurchase = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'payment_status' => PurchaseStatusEnum::CANCELLED,
    ]);

    $collection = new PurchaseCollection([
        $pendingPurchase1,
        $completedPurchase,
        $pendingPurchase2,
        $cancelledPurchase,
    ]);

    $result = $collection->byPaymentStatus(PurchaseStatusEnum::PENDING);

    expect($result)
        ->toBeInstanceOf(PurchaseCollection::class)
        ->toHaveCount(2)
        ->and($result->pluck('id')->toArray())
        ->toBe([$pendingPurchase1->id, $pendingPurchase2->id]);
});

test('by payment status returns empty collection when no matching status', function (): void {
    $store = Store::factory()->create();
    $supplier = Supplier::factory()->create();
    $user = User::factory()->create();

    $purchase = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'payment_status' => PurchaseStatusEnum::COMPLETED,
    ]);

    $collection = new PurchaseCollection([$purchase]);

    expect($collection->byPaymentStatus(PurchaseStatusEnum::PENDING))->toHaveCount(0);
});

test('pending returns only pending purchases', function (): void {
    $store = Store::factory()->create();
    $supplier = Supplier::factory()->create();
    $user = User::factory()->create();

    $pendingPurchase = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'payment_status' => PurchaseStatusEnum::PENDING,
    ]);

    $completedPurchase = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'payment_status' => PurchaseStatusEnum::COMPLETED,
    ]);

    $collection = new PurchaseCollection([$pendingPurchase, $completedPurchase]);

    $result = $collection->pending();

    expect($result)
        ->toBeInstanceOf(PurchaseCollection::class)
        ->toHaveCount(1)
        ->and($result->first()->id)->toBe($pendingPurchase->id);
});

test('pending returns empty collection when no pending purchases', function (): void {
    $store = Store::factory()->create();
    $supplier = Supplier::factory()->create();
    $user = User::factory()->create();

    $purchase = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'payment_status' => PurchaseStatusEnum::COMPLETED,
    ]);

    $collection = new PurchaseCollection([$purchase]);

    expect($collection->pending())->toHaveCount(0);
});

test('completed returns only completed purchases', function (): void {
    $store = Store::factory()->create();
    $supplier = Supplier::factory()->create();
    $user = User::factory()->create();

    $completedPurchase1 = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'payment_status' => PurchaseStatusEnum::COMPLETED,
    ]);

    $completedPurchase2 = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'payment_status' => PurchaseStatusEnum::COMPLETED,
    ]);

    $pendingPurchase = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'payment_status' => PurchaseStatusEnum::PENDING,
    ]);

    $collection = new PurchaseCollection([
        $completedPurchase1,
        $pendingPurchase,
        $completedPurchase2,
    ]);

    $result = $collection->completed();

    expect($result)
        ->toBeInstanceOf(PurchaseCollection::class)
        ->toHaveCount(2)
        ->and($result->pluck('id')->toArray())
        ->toBe([$completedPurchase1->id, $completedPurchase2->id]);
});

test('completed returns empty collection when no completed purchases', function (): void {
    $store = Store::factory()->create();
    $supplier = Supplier::factory()->create();
    $user = User::factory()->create();

    $purchase = Purchase::factory()->create([
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'created_by' => $user->id,
        'payment_status' => PurchaseStatusEnum::PENDING,
    ]);

    $collection = new PurchaseCollection([$purchase]);

    expect($collection->completed())->toHaveCount(0);
});
