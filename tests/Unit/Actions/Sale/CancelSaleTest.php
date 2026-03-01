<?php

declare(strict_types=1);

use App\Actions\Sale\CancelSale;
use App\Data\Sale\CancelSaleData;
use App\Enums\SaleStatusEnum;
use App\Exceptions\StateTransitionException;
use App\Models\Batch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;

it('cancels a pending sale', function (): void {
    $sale = Sale::factory()->pending()->create();

    $action = resolve(CancelSale::class);

    $action->handle($sale, new CancelSaleData(restock_items: false, note: null));

    expect($sale->fresh()->status)->toBe(SaleStatusEnum::Cancelled);
});

it('cancels a completed sale without restock', function (): void {
    $sale = Sale::factory()->completed()->create();
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);
    SaleItem::factory()->forSale($sale)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_price' => 500,
        'unit_cost' => 300,
    ]);

    $action = resolve(CancelSale::class);

    $action->handle($sale, new CancelSaleData(restock_items: false, note: 'Cancelling'));

    expect($sale->fresh()->status)->toBe(SaleStatusEnum::Cancelled)
        ->and($batch->fresh()->quantity)->toBe(100);
});

it('restocks items when cancelling completed sale', function (): void {
    $sale = Sale::factory()->completed()->create();
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);
    SaleItem::factory()->forSale($sale)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 20,
        'unit_price' => 500,
        'unit_cost' => 300,
    ]);

    $action = resolve(CancelSale::class);

    $action->handle($sale, new CancelSaleData(restock_items: true, note: null));

    expect($batch->fresh()->quantity)->toBe(120);
});

it('creates stock movements when restocking', function (): void {
    $sale = Sale::factory()->completed()->create();
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);
    SaleItem::factory()->forSale($sale)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 15,
        'unit_price' => 500,
        'unit_cost' => 300,
    ]);

    $action = resolve(CancelSale::class);

    $action->handle($sale, new CancelSaleData(restock_items: true, note: null));

    $movements = StockMovement::query()
        ->where('reference_type', Sale::class)
        ->where('reference_id', $sale->id)
        ->get();

    expect($movements)->toHaveCount(1);
});

it('throws exception when cancelling already cancelled sale', function (): void {
    $sale = Sale::factory()->cancelled()->create();

    $action = resolve(CancelSale::class);

    $action->handle($sale, new CancelSaleData(restock_items: false, note: null));
})->throws(StateTransitionException::class, 'Invalid state transition from "cancelled" to "Cancelled"');

it('updates note when cancelling', function (): void {
    $sale = Sale::factory()->pending()->create([
        'note' => 'Original note',
    ]);

    $action = resolve(CancelSale::class);

    $action->handle($sale, new CancelSaleData(restock_items: false, note: 'Cancelled due to customer request'));

    expect($sale->fresh()->note)->toBe('Cancelled due to customer request');
});

it('does not create stock movement for items without batch', function (): void {
    $sale = Sale::factory()->completed()->create();
    $product = App\Models\Product::factory()->create();
    SaleItem::factory()->forSale($sale)->create([
        'product_id' => $product->id,
        'batch_id' => null,
        'quantity' => 10,
        'unit_price' => 500,
        'unit_cost' => 300,
    ]);

    $action = resolve(CancelSale::class);

    $action->handle($sale, new CancelSaleData(restock_items: true, note: null));

    $movements = StockMovement::query()
        ->where('reference_type', Sale::class)
        ->where('reference_id', $sale->id)
        ->get();

    expect($movements)->toHaveCount(0);
});

it('does not restock when cancelling pending sale even with restock_items true', function (): void {
    $sale = Sale::factory()->pending()->create();
    $batch = Batch::factory()->withQuantity(100)->create([
        'warehouse_id' => $sale->warehouse_id,
    ]);
    SaleItem::factory()->forSale($sale)->create([
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 20,
        'unit_price' => 500,
        'unit_cost' => 300,
    ]);

    $action = resolve(CancelSale::class);

    $action->handle($sale, new CancelSaleData(restock_items: true, note: null));

    expect($sale->fresh()->status)->toBe(SaleStatusEnum::Cancelled)
        ->and($batch->fresh()->quantity)->toBe(100);

    $movements = StockMovement::query()
        ->where('reference_type', Sale::class)
        ->where('reference_id', $sale->id)
        ->get();

    expect($movements)->toHaveCount(0);
});
