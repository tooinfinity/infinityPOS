<?php

declare(strict_types=1);

use App\Actions\SaleReturn\CreateSaleReturn;
use App\Data\SaleReturn\SaleReturnData;
use App\Data\SaleReturn\SaleReturnItemData;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Unit;
use App\Models\Warehouse;

it('may create a sale return with required fields', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $customer = Customer::factory()->create();
    $sale = Sale::factory()->for($warehouse)->for($customer)->completed()->create();
    $sale->items()->create([
        'product_id' => $product->id,
        'quantity' => 10,
        'unit_price' => 10000,
        'unit_cost' => 5000,
        'subtotal' => 100000,
    ]);

    $action = resolve(CreateSaleReturn::class);

    $data = new SaleReturnData(
        sale_id: $sale->id,
        warehouse_id: $warehouse->id,
        return_date: now(),
        note: null,
        items: new Spatie\LaravelData\DataCollection(SaleReturnItemData::class, [
            new SaleReturnItemData(
                product_id: $product->id,
                batch_id: null,
                quantity: 2,
                unit_price: 10000,
            ),
        ]),
    );

    $return = $action->handle($data);

    expect($return)->toBeInstanceOf(SaleReturn::class)
        ->and($return->sale_id)->toBe($sale->id)
        ->and($return->warehouse_id)->toBe($warehouse->id)
        ->and($return->exists)->toBeTrue()
        ->and($return->items)->toHaveCount(1);
});

it('may create a sale return with multiple items', function (): void {
    $unit = Unit::factory()->create();
    $product1 = Product::factory()->for($unit)->create();
    $product2 = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $customer = Customer::factory()->create();
    $sale = Sale::factory()->for($warehouse)->for($customer)->completed()->create();
    $sale->items()->createMany([
        [
            'product_id' => $product1->id,
            'quantity' => 5,
            'unit_price' => 5000,
            'unit_cost' => 2500,
            'subtotal' => 25000,
        ],
        [
            'product_id' => $product2->id,
            'quantity' => 5,
            'unit_price' => 5000,
            'unit_cost' => 2500,
            'subtotal' => 25000,
        ],
    ]);

    $action = resolve(CreateSaleReturn::class);

    $data = new SaleReturnData(
        sale_id: $sale->id,
        warehouse_id: $warehouse->id,
        return_date: now(),
        note: null,
        items: new Spatie\LaravelData\DataCollection(SaleReturnItemData::class, [
            new SaleReturnItemData(
                product_id: $product1->id,
                batch_id: null,
                quantity: 1,
                unit_price: 5000,
            ),
            new SaleReturnItemData(
                product_id: $product2->id,
                batch_id: null,
                quantity: 1,
                unit_price: 5000,
            ),
        ]),
    );

    $return = $action->handle($data);

    expect($return->items)->toHaveCount(2);
});

it('may create a sale return with note', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $customer = Customer::factory()->create();
    $sale = Sale::factory()->for($warehouse)->for($customer)->completed()->create();
    $sale->items()->create([
        'product_id' => $product->id,
        'quantity' => 10,
        'unit_price' => 10000,
        'unit_cost' => 5000,
        'subtotal' => 100000,
    ]);

    $action = resolve(CreateSaleReturn::class);

    $data = new SaleReturnData(
        sale_id: $sale->id,
        warehouse_id: $warehouse->id,
        return_date: now(),
        note: 'Customer returned defective item',
        items: new Spatie\LaravelData\DataCollection(SaleReturnItemData::class, [
            new SaleReturnItemData(
                product_id: $product->id,
                batch_id: null,
                quantity: 2,
                unit_price: 10000,
            ),
        ]),
    );

    $return = $action->handle($data);

    expect($return->note)->toBe('Customer returned defective item');
});

it('may create a sale return with batch', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $batch = Batch::factory()->for($product)->create(['quantity' => 100]);
    $warehouse = Warehouse::factory()->create();
    $customer = Customer::factory()->create();
    $sale = Sale::factory()->for($warehouse)->for($customer)->completed()->create();
    $sale->items()->create([
        'product_id' => $product->id,
        'quantity' => 10,
        'unit_price' => 10000,
        'unit_cost' => 5000,
        'subtotal' => 100000,
    ]);

    $action = resolve(CreateSaleReturn::class);

    $data = new SaleReturnData(
        sale_id: $sale->id,
        warehouse_id: $warehouse->id,
        return_date: now(),
        note: null,
        items: new Spatie\LaravelData\DataCollection(SaleReturnItemData::class, [
            new SaleReturnItemData(
                product_id: $product->id,
                batch_id: $batch->id,
                quantity: 2,
                unit_price: 10000,
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
    $customer = Customer::factory()->create();
    $sale = Sale::factory()->for($warehouse)->for($customer)->completed()->create();
    $sale->items()->create([
        'product_id' => $product->id,
        'quantity' => 10,
        'unit_price' => 10000,
        'unit_cost' => 5000,
        'subtotal' => 100000,
    ]);

    $action = resolve(CreateSaleReturn::class);

    $data = new SaleReturnData(
        sale_id: $sale->id,
        warehouse_id: $warehouse->id,
        return_date: now(),
        note: null,
        items: new Spatie\LaravelData\DataCollection(SaleReturnItemData::class, [
            new SaleReturnItemData(
                product_id: $product->id,
                batch_id: null,
                quantity: 2,
                unit_price: 10000,
            ),
        ]),
    );

    $return = $action->handle($data);

    expect($return->reference_no)->toStartWith('SRN-')
        ->and($return->reference_no)->toHaveLength(17);
});

it('may create a sale return with custom return date', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $customer = Customer::factory()->create();
    $sale = Sale::factory()->for($warehouse)->for($customer)->completed()->create();
    $sale->items()->create([
        'product_id' => $product->id,
        'quantity' => 10,
        'unit_price' => 10000,
        'unit_cost' => 5000,
        'subtotal' => 100000,
    ]);
    $customDate = now()->addDays(3);

    $action = resolve(CreateSaleReturn::class);

    $data = new SaleReturnData(
        sale_id: $sale->id,
        warehouse_id: $warehouse->id,
        return_date: $customDate,
        note: null,
        items: new Spatie\LaravelData\DataCollection(SaleReturnItemData::class, [
            new SaleReturnItemData(
                product_id: $product->id,
                batch_id: null,
                quantity: 2,
                unit_price: 10000,
            ),
        ]),
    );

    $return = $action->handle($data);

    expect($return->return_date->toDateString())->toBe($customDate->toDateString());
});

it('may create SaleReturnData from model', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $customer = Customer::factory()->create();
    $sale = Sale::factory()->for($warehouse)->for($customer)->completed()->create();
    $saleReturn = SaleReturn::factory()->for($sale)->for($warehouse)->create();
    $saleReturn->items()->create([
        'product_id' => $product->id,
        'batch_id' => null,
        'quantity' => 2,
        'unit_price' => 10000,
        'subtotal' => 20000,
    ]);

    $data = SaleReturnData::fromModel($saleReturn);

    expect($data->sale_id)->toBe($sale->id)
        ->and($data->warehouse_id)->toBe($saleReturn->warehouse_id)
        ->and($data->items)->toHaveCount(1);
});
