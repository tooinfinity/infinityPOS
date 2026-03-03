<?php

declare(strict_types=1);

use App\Actions\Sale\CreateSale;
use App\Data\Sale\CreateSaleData;
use App\Data\Sale\SaleItemData;
use App\Enums\PaymentStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidBatchException;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Warehouse;
use Spatie\LaravelData\DataCollection;

it('creates a pending sale with items', function (): void {
    $customer = Customer::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    $action = resolve(CreateSale::class);

    $items = new DataCollection(SaleItemData::class, [
        new SaleItemData(
            product_id: $batch->product_id,
            batch_id: $batch->id,
            quantity: 10,
            unit_price: 500,
            unit_cost: 300
        ),
    ]);

    $data = new CreateSaleData(
        customer_id: $customer->id,
        warehouse_id: $warehouse->id,
        user_id: null,
        sale_date: now(),
        note: 'Test sale',
        items: $items,
    );

    $sale = $action->handle($data);

    expect($sale)
        ->toBeInstanceOf(Sale::class)
        ->and($sale->customer_id)->toBe($customer->id)
        ->and($sale->warehouse_id)->toBe($warehouse->id)
        ->and($sale->reference_no)->toStartWith('SAL-')
        ->and($sale->status)->toBe(SaleStatusEnum::Pending)
        ->and($sale->payment_status)->toBe(PaymentStatusEnum::Unpaid)
        ->and($sale->total_amount)->toBe(5000)
        ->and($sale->paid_amount)->toBe(0)
        ->and($sale->note)->toBe('Test sale')
        ->and($sale->exists)->toBeTrue();
});

it('auto-generates unique reference number', function (): void {
    $customer = Customer::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    $action = resolve(CreateSale::class);

    $items = new DataCollection(SaleItemData::class, [
        new SaleItemData(
            product_id: $batch->product_id,
            batch_id: $batch->id,
            quantity: 5,
            unit_price: 100,
            unit_cost: 50
        ),
    ]);

    $data = new CreateSaleData(
        customer_id: $customer->id,
        warehouse_id: $warehouse->id,
        user_id: null,
        sale_date: now(),
        note: null,
        items: $items,
    );

    $sale = $action->handle($data);

    expect($sale->reference_no)
        ->toStartWith('SAL-')
        ->and(mb_strlen($sale->reference_no))->toBeGreaterThan(10);
});

it('creates sale with multiple items', function (): void {
    $customer = Customer::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $batch1 = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();
    $batch2 = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    $action = resolve(CreateSale::class);

    $items = new DataCollection(SaleItemData::class, [
        new SaleItemData(
            product_id: $batch1->product_id,
            batch_id: $batch1->id,
            quantity: 10,
            unit_price: 100,
            unit_cost: 50
        ),
        new SaleItemData(
            product_id: $batch2->product_id,
            batch_id: $batch2->id,
            quantity: 5,
            unit_price: 200,
            unit_cost: 100
        ),
    ]);

    $data = new CreateSaleData(
        customer_id: $customer->id,
        warehouse_id: $warehouse->id,
        user_id: null,
        sale_date: now(),
        note: null,
        items: $items,
    );

    $sale = $action->handle($data);

    expect(SaleItem::query()->where('sale_id', $sale->id)->count())->toBe(2)
        ->and($sale->total_amount)->toBe(2000);
});

it('calculates correct subtotal for each item', function (): void {
    $customer = Customer::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    $action = resolve(CreateSale::class);

    $items = new DataCollection(SaleItemData::class, [
        new SaleItemData(
            product_id: $batch->product_id,
            batch_id: $batch->id,
            quantity: 15,
            unit_price: 250,
            unit_cost: 150
        ),
    ]);

    $data = new CreateSaleData(
        customer_id: $customer->id,
        warehouse_id: $warehouse->id,
        user_id: null,
        sale_date: now(),
        note: null,
        items: $items,
    );

    $sale = $action->handle($data);

    $item = SaleItem::query()->where('sale_id', $sale->id)->first();

    expect($item->subtotal)->toBe(3750);
});

it('throws RuntimeException when batch is not found', function (): void {
    $customer = Customer::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    $action = resolve(CreateSale::class);

    $items = new DataCollection(SaleItemData::class, [
        new SaleItemData(
            product_id: $batch->product_id,
            batch_id: 99999,
            quantity: 10,
            unit_price: 500,
            unit_cost: 300
        ),
    ]);

    $data = new CreateSaleData(
        customer_id: $customer->id,
        warehouse_id: $warehouse->id,
        user_id: null,
        sale_date: now(),
        note: null,
        items: $items,
    );

    expect(fn () => $action->handle($data))
        ->toThrow(InvalidBatchException::class, 'Batch 99999: not found');
});

it('throws RuntimeException when batch does not belong to product', function (): void {
    $customer = Customer::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();
    $otherProduct = App\Models\Product::factory()->create();

    $action = resolve(CreateSale::class);

    $items = new DataCollection(SaleItemData::class, [
        new SaleItemData(
            product_id: $otherProduct->id,
            batch_id: $batch->id,
            quantity: 10,
            unit_price: 500,
            unit_cost: 300
        ),
    ]);

    $data = new CreateSaleData(
        customer_id: $customer->id,
        warehouse_id: $warehouse->id,
        user_id: null,
        sale_date: now(),
        note: null,
        items: $items,
    );

    expect(fn () => $action->handle($data))
        ->toThrow(InvalidBatchException::class, 'Batch '.$batch->id.': does not belong to product "'.$batch->product->name.'"');
});

it('throws RuntimeException when batch is not in sale warehouse', function (): void {
    $customer = Customer::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $otherWarehouse = Warehouse::factory()->create();
    $batch = Batch::factory()->forWarehouse($otherWarehouse)->withQuantity(100)->create();

    $action = resolve(CreateSale::class);

    $items = new DataCollection(SaleItemData::class, [
        new SaleItemData(
            product_id: $batch->product_id,
            batch_id: $batch->id,
            quantity: 10,
            unit_price: 500,
            unit_cost: 300
        ),
    ]);

    $data = new CreateSaleData(
        customer_id: $customer->id,
        warehouse_id: $warehouse->id,
        user_id: null,
        sale_date: now(),
        note: null,
        items: $items,
    );

    expect(fn () => $action->handle($data))
        ->toThrow(InvalidBatchException::class, 'Batch '.$batch->id.': not in warehouse ID '.$warehouse->id);
});

it('throws RuntimeException when insufficient stock', function (): void {
    $customer = Customer::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(5)->create();

    $action = resolve(CreateSale::class);

    $items = new DataCollection(SaleItemData::class, [
        new SaleItemData(
            product_id: $batch->product_id,
            batch_id: $batch->id,
            quantity: 10,
            unit_price: 500,
            unit_cost: 300
        ),
    ]);

    $data = new CreateSaleData(
        customer_id: $customer->id,
        warehouse_id: $warehouse->id,
        user_id: null,
        sale_date: now(),
        note: null,
        items: $items,
    );

    expect(fn () => $action->handle($data))
        ->toThrow(InsufficientStockException::class, 'Insufficient stock in batch '.$batch->id.' for product "'.$batch->product->name.'". Required: 10, Available: 5');
});

it('stores sale in database', function (): void {
    $customer = Customer::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    $action = resolve(CreateSale::class);

    $items = new DataCollection(SaleItemData::class, [
        new SaleItemData(
            product_id: $batch->product_id,
            batch_id: $batch->id,
            quantity: 10,
            unit_price: 100,
            unit_cost: 50
        ),
    ]);

    $data = new CreateSaleData(
        customer_id: $customer->id,
        warehouse_id: $warehouse->id,
        user_id: null,
        sale_date: now(),
        note: null,
        items: $items,
    );

    $sale = $action->handle($data);

    expect($sale)->toBeInstanceOf(Sale::class)
        ->and($sale->exists)->toBeTrue();
});
