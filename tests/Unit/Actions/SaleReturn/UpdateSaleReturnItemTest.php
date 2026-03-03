<?php

declare(strict_types=1);

use App\Actions\SaleReturn\UpdateSaleReturnItem;
use App\Data\SaleReturn\UpdateSaleReturnItemData;
use App\Exceptions\StateTransitionException;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Warehouse;

it('updates item quantity in pending sale return', function (): void {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->forProduct($product)->withQuantity(100)->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create();
    SaleItem::factory()->forSale($sale)->forProduct($product)->forBatch($batch)->create([
        'quantity' => 20,
        'unit_price' => 100,
    ]);

    $saleReturn = SaleReturn::factory()->forSale($sale)->forWarehouse($warehouse)->pending()->create();
    $item = SaleReturnItem::factory()->forSaleReturn($saleReturn)->forProduct($product)->forBatch($batch)->create([
        'quantity' => 5,
        'unit_price' => 100,
        'subtotal' => 500,
    ]);

    $action = resolve(UpdateSaleReturnItem::class);

    $updated = $action->handle($item, new UpdateSaleReturnItemData(
        quantity: 10,
    ));

    expect($updated->quantity)->toBe(10)
        ->and($updated->subtotal)->toBe(1000);
});

it('updates item unit price in pending sale return', function (): void {
    $saleReturn = SaleReturn::factory()->pending()->create();
    $item = SaleReturnItem::factory()->forSaleReturn($saleReturn)->create([
        'quantity' => 5,
        'unit_price' => 100,
        'subtotal' => 500,
    ]);

    $action = resolve(UpdateSaleReturnItem::class);

    $updated = $action->handle($item, new UpdateSaleReturnItemData(
        unit_price: 200,
    ));

    expect($updated->unit_price)->toBe(200)
        ->and($updated->subtotal)->toBe(1000);
});

it('recalculates total amount when updating item', function (): void {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->forProduct($product)->withQuantity(100)->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create();
    SaleItem::factory()->forSale($sale)->forProduct($product)->forBatch($batch)->create([
        'quantity' => 20,
        'unit_price' => 100,
    ]);

    $saleReturn = SaleReturn::factory()->forSale($sale)->forWarehouse($warehouse)->pending()->create();
    $item = SaleReturnItem::factory()->forSaleReturn($saleReturn)->forProduct($product)->forBatch($batch)->create([
        'quantity' => 5,
        'unit_price' => 100,
        'subtotal' => 500,
    ]);

    $action = resolve(UpdateSaleReturnItem::class);

    $action->handle($item, new UpdateSaleReturnItemData(
        quantity: 10,
    ));

    expect($saleReturn->fresh()->total_amount)->toBe(1000);
});

it('throws exception when updating item in non-pending return', function (): void {
    $saleReturn = SaleReturn::factory()->completed()->create();
    $item = SaleReturnItem::factory()->forSaleReturn($saleReturn)->create([
        'quantity' => 5,
        'unit_price' => 100,
        'subtotal' => 500,
    ]);

    $action = resolve(UpdateSaleReturnItem::class);

    $action->handle($item, new UpdateSaleReturnItemData(
        quantity: 10,
    ));
})->throws(StateTransitionException::class, 'Invalid state transition from "completed" to "pending"');
