<?php

declare(strict_types=1);

use App\Actions\Sale\UpdateSaleItemAction;
use App\Data\Sale\UpdateSaleItemData;
use App\Models\Batch;
use App\Models\Sale;
use App\Models\SaleItem;

it('updates item in pending sale', function (): void {
    $sale = Sale::factory()->pending()->create();
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);
    $item = SaleItem::factory()->forSale($sale)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_price' => 500,
        'unit_cost' => 300,
        'subtotal' => 5000,
    ]);

    $action = resolve(UpdateSaleItemAction::class);

    $updatedItem = $action->handle($item, new UpdateSaleItemData(
        batch_id: null,
        quantity: 20,
        unit_price: null,
        unit_cost: null,
    ));

    expect($updatedItem->quantity)->toBe(20)
        ->and($updatedItem->subtotal)->toBe(10000);
});

it('recalculates sale total when updating item', function (): void {
    $sale = Sale::factory()->pending()->create([
        'total_amount' => 1000,
    ]);
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);
    SaleItem::factory()->forSale($sale)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_price' => 100,
        'unit_cost' => 50,
        'subtotal' => 1000,
    ]);

    $item = $sale->items->first();

    $action = resolve(UpdateSaleItemAction::class);

    $action->handle($item, new UpdateSaleItemData(
        batch_id: null,
        quantity: 20,
        unit_price: null,
        unit_cost: null,
    ));

    expect($sale->fresh()->total_amount)->toBe(2000);
});

it('throws exception when sale is not pending', function (): void {
    $sale = Sale::factory()->completed()->create();
    $item = SaleItem::factory()->forSale($sale)->create();

    $action = resolve(UpdateSaleItemAction::class);

    $action->handle($item, new UpdateSaleItemData(
        batch_id: null,
        quantity: 20,
        unit_price: null,
        unit_cost: null,
    ));
})->throws(RuntimeException::class, 'pending sales');

it('validates stock when increasing quantity', function (): void {
    $sale = Sale::factory()->pending()->create();
    $batch = Batch::factory()->withQuantity(10)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);
    $item = SaleItem::factory()->forSale($sale)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 5,
        'unit_price' => 500,
        'unit_cost' => 300,
        'subtotal' => 2500,
    ]);

    $action = resolve(UpdateSaleItemAction::class);

    $action->handle($item, new UpdateSaleItemData(
        batch_id: null,
        quantity: 20,
        unit_price: null,
        unit_cost: null,
    ));
})->throws(RuntimeException::class, 'Insufficient stock');

it('updates unit price correctly', function (): void {
    $sale = Sale::factory()->pending()->create();
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);
    $item = SaleItem::factory()->forSale($sale)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_price' => 500,
        'unit_cost' => 300,
        'subtotal' => 5000,
    ]);

    $action = resolve(UpdateSaleItemAction::class);

    $updatedItem = $action->handle($item, new UpdateSaleItemData(
        batch_id: null,
        quantity: null,
        unit_price: 750,
        unit_cost: null,
    ));

    expect($updatedItem->unit_price)->toBe(750)
        ->and($updatedItem->subtotal)->toBe(7500);
});

it('can change batch when updating item', function (): void {
    $sale = Sale::factory()->pending()->create();
    $batch1 = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);
    $batch2 = Batch::factory()->withQuantity(50)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);
    $item = SaleItem::factory()->forSale($sale)->create([
        'product_id' => $batch1->product_id,
        'batch_id' => $batch1->id,
        'quantity' => 10,
        'unit_price' => 500,
        'unit_cost' => 300,
        'subtotal' => 5000,
    ]);

    $action = resolve(UpdateSaleItemAction::class);

    $updatedItem = $action->handle($item, new UpdateSaleItemData(
        batch_id: $batch2->id,
        quantity: null,
        unit_price: null,
        unit_cost: null,
    ));

    expect($updatedItem->batch_id)->toBe($batch2->id);
});
