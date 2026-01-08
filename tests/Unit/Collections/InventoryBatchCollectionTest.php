<?php

declare(strict_types=1);

use App\Collections\InventoryBatchCollection;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\Store;

test('available returns only batches with remaining quantity', function (): void {
    $store = Store::factory()->create();
    $product = Product::factory()->create();
    $purchaseItem = PurchaseItem::factory()->create(['product_id' => $product->id]);

    $availableBatch = InventoryBatch::factory()->create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'purchase_item_id' => $purchaseItem->id,
        'quantity_remaining' => 10,
    ]);

    $emptyBatch = InventoryBatch::factory()->create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'purchase_item_id' => $purchaseItem->id,
        'quantity_remaining' => 0,
    ]);

    $collection = new InventoryBatchCollection([$availableBatch, $emptyBatch]);

    $result = $collection->available();

    expect($result)
        ->toBeInstanceOf(InventoryBatchCollection::class)
        ->toHaveCount(1)
        ->and($result->first()->id)->toBe($availableBatch->id);
});

test('available returns empty collection when no batches have remaining quantity', function (): void {
    $store = Store::factory()->create();
    $product = Product::factory()->create();
    $purchaseItem = PurchaseItem::factory()->create(['product_id' => $product->id]);

    $emptyBatch = InventoryBatch::factory()->create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'purchase_item_id' => $purchaseItem->id,
        'quantity_remaining' => 0,
    ]);

    $collection = new InventoryBatchCollection([$emptyBatch]);

    expect($collection->available())->toHaveCount(0);
});

test('fifo order returns batches sorted by batch date oldest first', function (): void {
    $store = Store::factory()->create();
    $product = Product::factory()->create();
    $purchaseItem = PurchaseItem::factory()->create(['product_id' => $product->id]);

    $newerBatch = InventoryBatch::factory()->create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'purchase_item_id' => $purchaseItem->id,
        'batch_date' => now()->subDays(1),
    ]);

    $oldestBatch = InventoryBatch::factory()->create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'purchase_item_id' => $purchaseItem->id,
        'batch_date' => now()->subDays(10),
    ]);

    $newestBatch = InventoryBatch::factory()->create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'purchase_item_id' => $purchaseItem->id,
        'batch_date' => now(),
    ]);

    $collection = new InventoryBatchCollection([$newerBatch, $oldestBatch, $newestBatch]);

    $result = $collection->fifoOrder();

    expect($result)
        ->toBeInstanceOf(InventoryBatchCollection::class)
        ->and($result->values()->pluck('id')->toArray())
        ->toBe([$oldestBatch->id, $newerBatch->id, $newestBatch->id]);
});

test('total available returns sum of remaining quantities', function (): void {
    $store = Store::factory()->create();
    $product = Product::factory()->create();
    $purchaseItem = PurchaseItem::factory()->create(['product_id' => $product->id]);

    $batch1 = InventoryBatch::factory()->create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'purchase_item_id' => $purchaseItem->id,
        'quantity_remaining' => 15,
    ]);

    $batch2 = InventoryBatch::factory()->create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'purchase_item_id' => $purchaseItem->id,
        'quantity_remaining' => 25,
    ]);

    $collection = new InventoryBatchCollection([$batch1, $batch2]);

    expect($collection->totalAvailable())->toBe(40);
});

test('total available returns zero for empty collection', function (): void {
    $collection = new InventoryBatchCollection([]);

    expect($collection->totalAvailable())->toBe(0);
});

test('average cost returns weighted average cost', function (): void {
    $store = Store::factory()->create();
    $product = Product::factory()->create();
    $purchaseItem = PurchaseItem::factory()->create(['product_id' => $product->id]);

    // Batch 1: 10 units at 100 cents = 1000 cents total
    $batch1 = InventoryBatch::factory()->create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'purchase_item_id' => $purchaseItem->id,
        'quantity_remaining' => 10,
        'unit_cost' => 100,
    ]);

    // Batch 2: 20 units at 200 cents = 4000 cents total
    $batch2 = InventoryBatch::factory()->create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'purchase_item_id' => $purchaseItem->id,
        'quantity_remaining' => 20,
        'unit_cost' => 200,
    ]);

    $collection = new InventoryBatchCollection([$batch1, $batch2]);

    // Total value: 5000 cents, Total quantity: 30
    // Average: 5000 / 30 = 166.67, rounded to 167
    expect($collection->averageCost())->toBe(167);
});

test('average cost returns zero when no quantity available', function (): void {
    $store = Store::factory()->create();
    $product = Product::factory()->create();
    $purchaseItem = PurchaseItem::factory()->create(['product_id' => $product->id]);

    $batch = InventoryBatch::factory()->create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'purchase_item_id' => $purchaseItem->id,
        'quantity_remaining' => 0,
        'unit_cost' => 100,
    ]);

    $collection = new InventoryBatchCollection([$batch]);

    expect($collection->averageCost())->toBe(0);
});

test('average cost returns zero for empty collection', function (): void {
    $collection = new InventoryBatchCollection([]);

    expect($collection->averageCost())->toBe(0);
});

test('total value returns sum of quantity times unit cost', function (): void {
    $store = Store::factory()->create();
    $product = Product::factory()->create();
    $purchaseItem = PurchaseItem::factory()->create(['product_id' => $product->id]);

    // Batch 1: 10 units at 100 cents = 1000 cents
    $batch1 = InventoryBatch::factory()->create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'purchase_item_id' => $purchaseItem->id,
        'quantity_remaining' => 10,
        'unit_cost' => 100,
    ]);

    // Batch 2: 5 units at 200 cents = 1000 cents
    $batch2 = InventoryBatch::factory()->create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'purchase_item_id' => $purchaseItem->id,
        'quantity_remaining' => 5,
        'unit_cost' => 200,
    ]);

    $collection = new InventoryBatchCollection([$batch1, $batch2]);

    expect($collection->totalValue())->toBe(2000);
});

test('total value returns zero for empty collection', function (): void {
    $collection = new InventoryBatchCollection([]);

    expect($collection->totalValue())->toBe(0);
});
