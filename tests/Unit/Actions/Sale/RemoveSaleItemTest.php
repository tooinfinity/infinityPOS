<?php

declare(strict_types=1);

use App\Actions\Sale\RemoveSaleItem;
use App\Exceptions\StateTransitionException;
use App\Models\Batch;
use App\Models\Sale;
use App\Models\SaleItem;

it('removes item from pending sale', function (): void {
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

    $itemId = $item->id;

    $action = resolve(RemoveSaleItem::class);

    $result = $action->handle($item);

    expect(SaleItem::query()->find($itemId))->toBeNull()
        ->and($result)->toBeInstanceOf(Sale::class);
});

it('recalculates total amount after removing item', function (): void {
    $sale = Sale::factory()->pending()->create([
        'total_amount' => 1500,
    ]);
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);
    SaleItem::factory()->forSale($sale)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_price' => 500,
        'unit_cost' => 300,
        'subtotal' => 5000,
    ]);
    SaleItem::factory()->forSale($sale)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 5,
        'unit_price' => 100,
        'unit_cost' => 50,
        'subtotal' => 500,
    ]);

    $itemToRemove = $sale->items()->first();

    $action = resolve(RemoveSaleItem::class);

    $action->handle($itemToRemove);

    expect($sale->fresh()->total_amount)->toBe(500);
});

it('throws exception when sale is not pending', function (): void {
    $sale = Sale::factory()->completed()->create();
    $item = SaleItem::factory()->forSale($sale)->create();

    $action = resolve(RemoveSaleItem::class);

    $action->handle($item);
})->throws(StateTransitionException::class, 'Invalid state transition from "completed" to "pending"');

it('deletes item from database', function (): void {
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

    $itemId = $item->id;

    $action = resolve(RemoveSaleItem::class);

    $action->handle($item);

    expect(SaleItem::query()->find($itemId))->toBeNull();
});
