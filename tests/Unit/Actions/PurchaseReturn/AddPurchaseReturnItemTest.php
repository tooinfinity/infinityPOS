<?php

declare(strict_types=1);

use App\Actions\PurchaseReturn\AddPurchaseReturnItem;
use App\Data\PurchaseReturn\PurchaseReturnItemData;
use App\Exceptions\InvalidOperationException;
use App\Exceptions\ItemNotFoundException;
use App\Exceptions\StateTransitionException;
use App\Models\Batch;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\Warehouse;

it('adds item to pending purchase return', function (): void {
    $warehouse = Warehouse::factory()->create();
    $purchase = Purchase::factory()->forWarehouse($warehouse)->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    PurchaseItem::query()->forceCreate([
        'purchase_id' => $purchase->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_cost' => 200,
        'subtotal' => 2000,
        'received_quantity' => 10,
    ]);

    $purchaseReturn = PurchaseReturn::factory()->forPurchase($purchase)->pending()->create();

    $action = resolve(AddPurchaseReturnItem::class);

    $item = $action->handle($purchaseReturn, new PurchaseReturnItemData(
        product_id: $batch->product_id,
        batch_id: $batch->id,
        quantity: 5,
        unit_cost: 200,
    ));

    expect($item)
        ->toBeInstanceOf(App\Models\PurchaseReturnItem::class)
        ->and($item->purchase_return_id)->toBe($purchaseReturn->id)
        ->and($item->quantity)->toBe(5)
        ->and($item->subtotal)->toBe(1000);
});

it('recalculates total amount when adding item', function (): void {
    $warehouse = Warehouse::factory()->create();
    $purchase = Purchase::factory()->forWarehouse($warehouse)->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    PurchaseItem::query()->forceCreate([
        'purchase_id' => $purchase->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_cost' => 500,
        'subtotal' => 5000,
        'received_quantity' => 10,
    ]);

    $purchaseReturn = PurchaseReturn::factory()->forPurchase($purchase)->pending()->create(['total_amount' => 0]);

    $action = resolve(AddPurchaseReturnItem::class);

    $action->handle($purchaseReturn, new PurchaseReturnItemData(
        product_id: $batch->product_id,
        batch_id: $batch->id,
        quantity: 10,
        unit_cost: 500,
    ));

    expect($purchaseReturn->fresh()->total_amount)->toBe(5000);
});

it('throws exception when adding item to non-pending return', function (): void {
    $warehouse = Warehouse::factory()->create();
    $purchase = Purchase::factory()->forWarehouse($warehouse)->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    PurchaseItem::query()->forceCreate([
        'purchase_id' => $purchase->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_cost' => 200,
        'subtotal' => 2000,
        'received_quantity' => 10,
    ]);

    $purchaseReturn = PurchaseReturn::factory()->forPurchase($purchase)->completed()->create();

    $action = resolve(AddPurchaseReturnItem::class);

    $action->handle($purchaseReturn, new PurchaseReturnItemData(
        product_id: $batch->product_id,
        batch_id: $batch->id,
        quantity: 5,
        unit_cost: 200,
    ));
})->throws(StateTransitionException::class, 'Invalid state transition from "completed" to "pending"');

it('throws exception when product not in original purchase', function (): void {
    $warehouse = Warehouse::factory()->create();
    $purchase = Purchase::factory()->forWarehouse($warehouse)->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    PurchaseItem::query()->forceCreate([
        'purchase_id' => $purchase->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 10,
        'unit_cost' => 200,
        'subtotal' => 2000,
        'received_quantity' => 10,
    ]);

    $purchaseReturn = PurchaseReturn::factory()->forPurchase($purchase)->pending()->create();

    $action = resolve(AddPurchaseReturnItem::class);

    $otherBatch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    $action->handle($purchaseReturn, new PurchaseReturnItemData(
        product_id: $otherBatch->product_id,
        batch_id: $otherBatch->id,
        quantity: 5,
        unit_cost: 200,
    ));
})->throws(ItemNotFoundException::class, 'Product is not part of the original purchase');

it('throws exception when returning more than purchased', function (): void {
    $warehouse = Warehouse::factory()->create();
    $purchase = Purchase::factory()->forWarehouse($warehouse)->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->withQuantity(100)->create();

    PurchaseItem::query()->forceCreate([
        'purchase_id' => $purchase->id,
        'product_id' => $batch->product_id,
        'batch_id' => $batch->id,
        'quantity' => 5,
        'unit_cost' => 200,
        'subtotal' => 1000,
        'received_quantity' => 5,
    ]);

    $purchaseReturn = PurchaseReturn::factory()->forPurchase($purchase)->pending()->create();

    $action = resolve(AddPurchaseReturnItem::class);

    $action->handle($purchaseReturn, new PurchaseReturnItemData(
        product_id: $batch->product_id,
        batch_id: $batch->id,
        quantity: 10,
        unit_cost: 200,
    ));
})->throws(InvalidOperationException::class, 'Cannot return item. Cannot return more than originally purchased. Original: 5, Already returned: 0, Remaining: 5');
