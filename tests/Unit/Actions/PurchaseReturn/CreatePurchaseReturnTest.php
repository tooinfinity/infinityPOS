<?php

declare(strict_types=1);

use App\Actions\PurchaseReturn\CreatePurchaseReturn;
use App\Data\PurchaseReturn\PurchaseReturnData;
use App\Data\PurchaseReturn\PurchaseReturnItemData;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;

it('may create a purchase return with required fields', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();
    $purchase = Purchase::factory()->for($warehouse)->for($supplier)->received()->create();
    $purchase->items()->create([
        'product_id' => $product->id,
        'quantity' => 10,
        'received_quantity' => 10,
        'unit_cost' => 10000,
        'subtotal' => 100000,
    ]);

    $action = resolve(CreatePurchaseReturn::class);

    $data = new PurchaseReturnData(
        purchase_id: $purchase->id,
        warehouse_id: $warehouse->id,
        return_date: now()->toDateString(),
        note: null,
        items: new Spatie\LaravelData\DataCollection(PurchaseReturnItemData::class, [
            new PurchaseReturnItemData(
                product_id: $product->id,
                batch_id: null,
                quantity: 2,
                unit_cost: 10000,
            ),
        ]),
    );

    $return = $action->handle($data);

    expect($return)->toBeInstanceOf(PurchaseReturn::class)
        ->and($return->purchase_id)->toBe($purchase->id)
        ->and($return->warehouse_id)->toBe($warehouse->id)
        ->and($return->exists)->toBeTrue()
        ->and($return->items)->toHaveCount(1);
});

it('may create a purchase return with multiple items', function (): void {
    $unit = Unit::factory()->create();
    $product1 = Product::factory()->for($unit)->create();
    $product2 = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();
    $purchase = Purchase::factory()->for($warehouse)->for($supplier)->received()->create();
    $purchase->items()->createMany([
        [
            'product_id' => $product1->id,
            'quantity' => 10,
            'received_quantity' => 10,
            'unit_cost' => 5000,
            'subtotal' => 50000,
        ],
        [
            'product_id' => $product2->id,
            'quantity' => 10,
            'received_quantity' => 10,
            'unit_cost' => 5000,
            'subtotal' => 50000,
        ],
    ]);

    $action = resolve(CreatePurchaseReturn::class);

    $data = new PurchaseReturnData(
        purchase_id: $purchase->id,
        warehouse_id: $warehouse->id,
        return_date: now()->toDateString(),
        note: null,
        items: new Spatie\LaravelData\DataCollection(PurchaseReturnItemData::class, [
            new PurchaseReturnItemData(
                product_id: $product1->id,
                batch_id: null,
                quantity: 1,
                unit_cost: 5000,
            ),
            new PurchaseReturnItemData(
                product_id: $product2->id,
                batch_id: null,
                quantity: 1,
                unit_cost: 5000,
            ),
        ]),
    );

    $return = $action->handle($data);

    expect($return->items)->toHaveCount(2);
});

it('may create a purchase return with note', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();
    $purchase = Purchase::factory()->for($warehouse)->for($supplier)->received()->create();
    $purchase->items()->create([
        'product_id' => $product->id,
        'quantity' => 10,
        'received_quantity' => 10,
        'unit_cost' => 10000,
        'subtotal' => 100000,
    ]);

    $action = resolve(CreatePurchaseReturn::class);

    $data = new PurchaseReturnData(
        purchase_id: $purchase->id,
        warehouse_id: $warehouse->id,
        return_date: now()->toDateString(),
        note: 'Items damaged in transit',
        items: new Spatie\LaravelData\DataCollection(PurchaseReturnItemData::class, [
            new PurchaseReturnItemData(
                product_id: $product->id,
                batch_id: null,
                quantity: 2,
                unit_cost: 10000,
            ),
        ]),
    );

    $return = $action->handle($data);

    expect($return->note)->toBe('Items damaged in transit');
});

it('may create a purchase return with batch', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $batch = Batch::factory()->for($product)->create(['quantity' => 100]);
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();
    $purchase = Purchase::factory()->for($warehouse)->for($supplier)->received()->create();
    $purchase->items()->create([
        'product_id' => $product->id,
        'quantity' => 10,
        'received_quantity' => 10,
        'unit_cost' => 10000,
        'subtotal' => 100000,
    ]);

    $action = resolve(CreatePurchaseReturn::class);

    $data = new PurchaseReturnData(
        purchase_id: $purchase->id,
        warehouse_id: $warehouse->id,
        return_date: now()->toDateString(),
        note: null,
        items: new Spatie\LaravelData\DataCollection(PurchaseReturnItemData::class, [
            new PurchaseReturnItemData(
                product_id: $product->id,
                batch_id: $batch->id,
                quantity: 2,
                unit_cost: 10000,
            ),
        ]),
    );

    $return = $action->handle($data);

    expect($return->items->first()->batch_id)->toBe($batch->id);
});

it('generates reference number', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();
    $purchase = Purchase::factory()->for($warehouse)->for($supplier)->received()->create();
    $purchase->items()->create([
        'product_id' => $product->id,
        'quantity' => 10,
        'received_quantity' => 10,
        'unit_cost' => 10000,
        'subtotal' => 100000,
    ]);

    $action = resolve(CreatePurchaseReturn::class);

    $data = new PurchaseReturnData(
        purchase_id: $purchase->id,
        warehouse_id: $warehouse->id,
        return_date: now()->toDateString(),
        note: null,
        items: new Spatie\LaravelData\DataCollection(PurchaseReturnItemData::class, [
            new PurchaseReturnItemData(
                product_id: $product->id,
                batch_id: null,
                quantity: 2,
                unit_cost: 10000,
            ),
        ]),
    );

    $return = $action->handle($data);

    expect($return->reference_no)->toStartWith('PRN-')
        ->and($return->reference_no)->toHaveLength(17);
});

it('may create a purchase return with custom return date', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();
    $purchase = Purchase::factory()->for($warehouse)->for($supplier)->received()->create();
    $purchase->items()->create([
        'product_id' => $product->id,
        'quantity' => 10,
        'received_quantity' => 10,
        'unit_cost' => 10000,
        'subtotal' => 100000,
    ]);
    $customDate = now()->addDays(2)->toDateString();

    $action = resolve(CreatePurchaseReturn::class);

    $data = new PurchaseReturnData(
        purchase_id: $purchase->id,
        warehouse_id: $warehouse->id,
        return_date: $customDate,
        note: null,
        items: new Spatie\LaravelData\DataCollection(PurchaseReturnItemData::class, [
            new PurchaseReturnItemData(
                product_id: $product->id,
                batch_id: null,
                quantity: 2,
                unit_cost: 10000,
            ),
        ]),
    );

    $return = $action->handle($data);

    expect($return->return_date->toDateString())->toBe($customDate);
});

it('may create PurchaseReturnData from model', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();
    $purchase = Purchase::factory()->for($warehouse)->for($supplier)->received()->create();
    $purchaseReturn = PurchaseReturn::factory()->for($purchase)->for($warehouse)->create();
    $purchaseReturn->items()->create([
        'product_id' => $product->id,
        'batch_id' => null,
        'quantity' => 2,
        'unit_cost' => 10000,
        'subtotal' => 20000,
    ]);

    $data = PurchaseReturnData::fromModel($purchaseReturn);

    expect($data->purchase_id)->toBe($purchase->id)
        ->and($data->warehouse_id)->toBe($purchaseReturn->warehouse_id)
        ->and($data->items)->toHaveCount(1);
});
