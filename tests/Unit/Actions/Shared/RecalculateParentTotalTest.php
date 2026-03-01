<?php

declare(strict_types=1);

use App\Actions\Shared\RecalculateParentTotal;
use App\Models\Batch;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Warehouse;

it('recalculates total amount for sale', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create(['total_amount' => 0]);
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

    SaleItem::query()->forceCreate([
        'sale_id' => $sale->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 5,
        'unit_price' => 150,
        'unit_cost' => 75,
        'subtotal' => 750,
    ]);

    $action = resolve(RecalculateParentTotal::class);
    $action->handle($sale);

    expect($sale->fresh()->total_amount)->toBe(2750);
});

it('recalculates total amount for sale return', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create();
    $saleReturn = SaleReturn::factory()->forSale($sale)->pending()->create(['total_amount' => 0]);
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    SaleReturnItem::query()->forceCreate([
        'sale_return_id' => $saleReturn->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 5,
        'unit_price' => 200,
        'subtotal' => 1000,
    ]);

    SaleReturnItem::query()->forceCreate([
        'sale_return_id' => $saleReturn->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 3,
        'unit_price' => 150,
        'subtotal' => 450,
    ]);

    $action = resolve(RecalculateParentTotal::class);
    $action->handle($saleReturn);

    expect($saleReturn->fresh()->total_amount)->toBe(1450);
});

it('recalculates total amount for purchase', function (): void {
    $warehouse = Warehouse::factory()->create();
    $purchase = Purchase::factory()->forWarehouse($warehouse)->pending()->create(['total_amount' => 0]);
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    PurchaseItem::query()->forceCreate([
        'purchase_id' => $purchase->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'received_quantity' => 10,
        'unit_cost' => 100,
        'subtotal' => 2000,
    ]);

    PurchaseItem::query()->forceCreate([
        'purchase_id' => $purchase->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 5,
        'received_quantity' => 5,
        'unit_cost' => 75,
        'subtotal' => 750,
    ]);

    $action = resolve(RecalculateParentTotal::class);
    $action->handle($purchase);

    expect($purchase->fresh()->total_amount)->toBe(2750);
});

it('recalculates total amount for purchase return', function (): void {
    $warehouse = Warehouse::factory()->create();
    $purchase = Purchase::factory()->forWarehouse($warehouse)->create();
    $purchaseReturn = PurchaseReturn::factory()->forPurchase($purchase)->pending()->create(['total_amount' => 0]);
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    PurchaseReturnItem::query()->forceCreate([
        'purchase_return_id' => $purchaseReturn->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 5,
        'unit_cost' => 200,
        'subtotal' => 1000,
    ]);

    PurchaseReturnItem::query()->forceCreate([
        'purchase_return_id' => $purchaseReturn->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 3,
        'unit_cost' => 150,
        'subtotal' => 450,
    ]);

    $action = resolve(RecalculateParentTotal::class);
    $action->handle($purchaseReturn);

    expect($purchaseReturn->fresh()->total_amount)->toBe(1450);
});

it('handles zero items', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create(['total_amount' => 1000]);

    $action = resolve(RecalculateParentTotal::class);
    $action->handle($sale);

    expect($sale->fresh()->total_amount)->toBe(0);
});

it('refreshes model before calculating', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create(['total_amount' => 0]);
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

    $action = resolve(RecalculateParentTotal::class);
    $action->handle($sale);

    expect($sale->fresh()->total_amount)->toBe(2000);
});
