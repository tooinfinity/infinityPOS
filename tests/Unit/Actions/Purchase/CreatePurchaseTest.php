<?php

declare(strict_types=1);

use App\Actions\Purchase\CreatePurchase;
use App\Data\Purchase\PurchaseData;
use App\Data\Purchase\PurchaseItemData;
use App\Enums\PurchaseStatusEnum;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;

it('may create a purchase with required fields', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();

    $action = resolve(CreatePurchase::class);

    $data = new PurchaseData(
        supplier_id: $supplier->id,
        warehouse_id: $warehouse->id,
        status: PurchaseStatusEnum::Pending,
        purchase_date: now(),
        total_amount: 100000,
        note: null,
        items: new Spatie\LaravelData\DataCollection(PurchaseItemData::class, [
            new PurchaseItemData(
                product_id: $product->id,
                quantity: 10,
                unit_cost: 10000,
                expires_at: null,
            ),
        ]),
    );

    $purchase = $action->handle($data);

    expect($purchase)->toBeInstanceOf(Purchase::class)
        ->and($purchase->supplier_id)->toBe($supplier->id)
        ->and($purchase->warehouse_id)->toBe($warehouse->id)
        ->and($purchase->status)->toBe(PurchaseStatusEnum::Pending)
        ->and($purchase->total_amount)->toBe(100000)
        ->and($purchase->paid_amount)->toBe(0)
        ->and($purchase->exists)->toBeTrue()
        ->and($purchase->items)->toHaveCount(1);
});

it('may create a purchase with multiple items', function (): void {
    $unit = Unit::factory()->create();
    $product1 = Product::factory()->for($unit)->create();
    $product2 = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();

    $action = resolve(CreatePurchase::class);

    $data = new PurchaseData(
        supplier_id: $supplier->id,
        warehouse_id: $warehouse->id,
        status: PurchaseStatusEnum::Pending,
        purchase_date: now(),
        total_amount: 50000,
        note: null,
        items: new Spatie\LaravelData\DataCollection(PurchaseItemData::class, [
            new PurchaseItemData(
                product_id: $product1->id,
                quantity: 5,
                unit_cost: 5000,
                expires_at: null,
            ),
            new PurchaseItemData(
                product_id: $product2->id,
                quantity: 5,
                unit_cost: 5000,
                expires_at: null,
            ),
        ]),
    );

    $purchase = $action->handle($data);

    expect($purchase->items)->toHaveCount(2);
});

it('may create a purchase with note', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();

    $action = resolve(CreatePurchase::class);

    $data = new PurchaseData(
        supplier_id: $supplier->id,
        warehouse_id: $warehouse->id,
        status: PurchaseStatusEnum::Pending,
        purchase_date: now(),
        total_amount: 100000,
        note: 'Test purchase note',
        items: new Spatie\LaravelData\DataCollection(PurchaseItemData::class, [
            new PurchaseItemData(
                product_id: $product->id,
                quantity: 10,
                unit_cost: 10000,
                expires_at: null,
            ),
        ]),
    );

    $purchase = $action->handle($data);

    expect($purchase->note)->toBe('Test purchase note');
});

it('may create a purchase with expiration dates on items', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();

    $action = resolve(CreatePurchase::class);

    $data = new PurchaseData(
        supplier_id: $supplier->id,
        warehouse_id: $warehouse->id,
        status: PurchaseStatusEnum::Pending,
        purchase_date: now(),
        total_amount: 100000,
        note: null,
        items: new Spatie\LaravelData\DataCollection(PurchaseItemData::class, [
            new PurchaseItemData(
                product_id: $product->id,
                quantity: 10,
                unit_cost: 10000,
                expires_at: now()->addMonths(6)->toDateString(),
            ),
        ]),
    );

    $purchase = $action->handle($data);

    expect($purchase->items->first()->expires_at)->not->toBeNull();
});

it('may create a purchase with paid amount', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();

    $action = resolve(CreatePurchase::class);

    $data = new PurchaseData(
        supplier_id: $supplier->id,
        warehouse_id: $warehouse->id,
        status: PurchaseStatusEnum::Pending,
        purchase_date: now(),
        total_amount: 100000,
        note: null,
        items: new Spatie\LaravelData\DataCollection(PurchaseItemData::class, [
            new PurchaseItemData(
                product_id: $product->id,
                quantity: 10,
                unit_cost: 10000,
                expires_at: null,
            ),
        ]),
    );

    $purchase = $action->handle($data);

    expect($purchase->paid_amount)->toBe(0);
});

it('generates reference number', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();

    $action = resolve(CreatePurchase::class);

    $data = new PurchaseData(
        supplier_id: $supplier->id,
        warehouse_id: $warehouse->id,
        status: PurchaseStatusEnum::Pending,
        purchase_date: now(),
        total_amount: 100000,
        note: null,
        items: new Spatie\LaravelData\DataCollection(PurchaseItemData::class, [
            new PurchaseItemData(
                product_id: $product->id,
                quantity: 10,
                unit_cost: 10000,
                expires_at: null,
            ),
        ]),
    );

    $purchase = $action->handle($data);

    expect($purchase->reference_no)->toStartWith('PUR-')
        ->and($purchase->reference_no)->toHaveLength(17);
});

it('may create PurchaseData from model', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();
    $purchase = Purchase::factory()->for($warehouse)->for($supplier)->create();
    $purchase->items()->create([
        'product_id' => $product->id,
        'quantity' => 10,
        'received_quantity' => 0,
        'unit_cost' => 10000,
        'subtotal' => 100000,
    ]);

    $data = PurchaseData::fromModel($purchase);

    expect($data->supplier_id)->toBe($supplier->id)
        ->and($data->warehouse_id)->toBe($warehouse->id)
        ->and($data->items)->toHaveCount(1);
});
