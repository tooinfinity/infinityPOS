<?php

declare(strict_types=1);

use App\Actions\Sale\AddSaleItem;
use App\Data\Sale\SaleItemData;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidBatchException;
use App\Exceptions\StateTransitionException;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Warehouse;

it('adds item to pending sale', function (): void {
    $sale = Sale::factory()->pending()->create();
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);

    $action = resolve(AddSaleItem::class);

    $item = $action->handle($sale, new SaleItemData(
        product_id: $batch->product_id,
        batch_id: $batch->id,
        quantity: 10,
        unit_price: 500,
        unit_cost: 300,
    ));

    expect($item)
        ->toBeInstanceOf(SaleItem::class)
        ->and($item->sale_id)->toBe($sale->id)
        ->and($item->quantity)->toBe(10)
        ->and($item->unit_price)->toBe(500)
        ->and($item->subtotal)->toBe(5000);
});

it('recalculates total amount when adding item', function (): void {
    $sale = Sale::factory()->pending()->create([
        'total_amount' => 1000,
    ]);
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);
    SaleItem::factory()->forSale($sale)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 2,
        'unit_price' => 500,
        'unit_cost' => 300,
        'subtotal' => 1000,
    ]);

    $action = resolve(AddSaleItem::class);

    $action->handle($sale, new SaleItemData(
        product_id: $batch->product_id,
        batch_id: $batch->id,
        quantity: 5,
        unit_price: 200,
        unit_cost: 100,
    ));

    expect($sale->fresh()->total_amount)->toBe(2000);
});

it('throws exception when sale is not pending', function (): void {
    $sale = Sale::factory()->completed()->create();
    $batch = Batch::factory()->withQuantity(100)->create();

    $action = resolve(AddSaleItem::class);

    $action->handle($sale, new SaleItemData(
        product_id: $batch->product_id,
        batch_id: $batch->id,
        quantity: 10,
        unit_price: 500,
        unit_cost: 300,
    ));
})->throws(StateTransitionException::class, 'Invalid state transition from "completed" to "pending"');

it('throws exception when insufficient stock', function (): void {
    $sale = Sale::factory()->pending()->create();
    $batch = Batch::factory()->withQuantity(5)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);

    $action = resolve(AddSaleItem::class);

    $action->handle($sale, new SaleItemData(
        product_id: $batch->product_id,
        batch_id: $batch->id,
        quantity: 10,
        unit_price: 500,
        unit_cost: 300,
    ));
})->throws(InsufficientStockException::class, 'Insufficient stock');

it('throws exception when batch not found', function (): void {
    $sale = Sale::factory()->pending()->create();

    $action = resolve(AddSaleItem::class);

    $action->handle($sale, new SaleItemData(
        product_id: 999,
        batch_id: 999,
        quantity: 10,
        unit_price: 500,
        unit_cost: 300,
    ));
})->throws(InvalidBatchException::class, 'Batch 999: not found');

it('throws exception when batch belongs to different product', function (): void {
    $sale = Sale::factory()->pending()->create();
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);
    $otherProduct = Product::factory()->create();

    $action = resolve(AddSaleItem::class);

    $action->handle($sale, new SaleItemData(
        product_id: $otherProduct->id,
        batch_id: $batch->id,
        quantity: 10,
        unit_price: 500,
        unit_cost: 300,
    ));
})->throws(InvalidBatchException::class, 'Batch 1: does not belong to product 2');

it('throws exception when batch is in different warehouse', function (): void {
    $sale = Sale::factory()->pending()->create();
    $differentWarehouse = Warehouse::factory()->create();
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $differentWarehouse->id,
    ]);

    $action = resolve(AddSaleItem::class);

    $action->handle($sale, new SaleItemData(
        product_id: $batch->product_id,
        batch_id: $batch->id,
        quantity: 10,
        unit_price: 500,
        unit_cost: 300,
    ));
})->throws(InvalidBatchException::class, 'Batch 1: not in warehouse 1');
