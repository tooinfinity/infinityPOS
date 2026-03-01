<?php

declare(strict_types=1);

use App\Actions\SaleReturn\AddSaleReturnItem;
use App\Data\SaleReturn\SaleReturnItemData;
use App\Exceptions\InvalidOperationException;
use App\Exceptions\ItemNotFoundException;
use App\Exceptions\RefundNotAllowedException;
use App\Exceptions\StateTransitionException;
use App\Models\Batch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\Warehouse;

it('adds item to pending sale return', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    SaleItem::query()->forceCreate([
        'sale_id' => $sale->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_price' => 200,
        'unit_cost' => 100,
        'subtotal' => 2000,
    ]);

    $saleReturn = SaleReturn::factory()->forSale($sale)->pending()->create();

    $action = resolve(AddSaleReturnItem::class);

    $item = $action->handle($saleReturn, new SaleReturnItemData(
        product_id: $batch->product_id,
        batch_id: $batch->id,
        quantity: 5,
        unit_price: 200,
    ));

    expect($item)
        ->toBeInstanceOf(App\Models\SaleReturnItem::class)
        ->and($item->sale_return_id)->toBe($saleReturn->id)
        ->and($item->product_id)->toBe($batch->product_id)
        ->and($item->quantity)->toBe(5)
        ->and($item->subtotal)->toBe(1000);
});

it('recalculates total amount when adding item', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    SaleItem::query()->forceCreate([
        'sale_id' => $sale->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_price' => 500,
        'unit_cost' => 250,
        'subtotal' => 5000,
    ]);

    $saleReturn = SaleReturn::factory()->forSale($sale)->pending()->create([
        'total_amount' => 0,
    ]);

    $action = resolve(AddSaleReturnItem::class);

    $action->handle($saleReturn, new SaleReturnItemData(
        product_id: $batch->product_id,
        batch_id: $batch->id,
        quantity: 10,
        unit_price: 500,
    ));

    expect($saleReturn->fresh()->total_amount)->toBe(5000);
});

it('throws exception when adding item to non-pending return', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    SaleItem::query()->forceCreate([
        'sale_id' => $sale->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_price' => 200,
        'unit_cost' => 100,
        'subtotal' => 2000,
    ]);

    $saleReturn = SaleReturn::factory()->forSale($sale)->completed()->create();

    $action = resolve(AddSaleReturnItem::class);

    $action->handle($saleReturn, new SaleReturnItemData(
        product_id: $batch->product_id,
        batch_id: $batch->id,
        quantity: 5,
        unit_price: 200,
    ));
})->throws(StateTransitionException::class, 'Invalid state transition from "completed" to "pending"');

it('throws exception when product not in original sale', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    SaleItem::query()->forceCreate([
        'sale_id' => $sale->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_price' => 200,
        'unit_cost' => 100,
        'subtotal' => 2000,
    ]);

    $saleReturn = SaleReturn::factory()->forSale($sale)->pending()->create();

    $action = resolve(AddSaleReturnItem::class);

    $otherBatch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    $action->handle($saleReturn, new SaleReturnItemData(
        product_id: $otherBatch->product_id,
        batch_id: $otherBatch->id,
        quantity: 5,
        unit_price: 200,
    ));
})->throws(ItemNotFoundException::class, 'Product is not part of the original sale');

it('throws exception when returning more than purchased', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    SaleItem::query()->forceCreate([
        'sale_id' => $sale->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 5,
        'unit_price' => 200,
        'unit_cost' => 100,
        'subtotal' => 1000,
    ]);

    $saleReturn = SaleReturn::factory()->forSale($sale)->pending()->create();

    $action = resolve(AddSaleReturnItem::class);

    $action->handle($saleReturn, new SaleReturnItemData(
        product_id: $batch->product_id,
        batch_id: $batch->id,
        quantity: 10,
        unit_price: 200,
    ));
})->throws(InvalidOperationException::class, 'annot return item. Cannot return more than originally purchased. Original: 5, Already returned: 0, Remaining: 5');
