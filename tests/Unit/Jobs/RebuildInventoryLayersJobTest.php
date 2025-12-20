<?php

declare(strict_types=1);

use App\Jobs\Inventory\RebuildInventoryLayersJob;
use App\Models\InventoryLayer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;

it('rebuilds inventory layers from stock movements', function (): void {
    $user = User::factory()->create();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $product = Product::factory()->create(['created_by' => $user->id, 'cost' => 100]);

    $purchase = Purchase::factory()->create([
        'store_id' => $store->id,
        'created_by' => $user->id,
    ]);

    $purchaseItem = PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'quantity' => 10,
        'cost' => 150,
        'total' => 1500,
    ]);

    // Create stock movements (incoming)
    StockMovement::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'quantity' => 10,
        'source_type' => Purchase::class,
        'source_id' => $purchase->id,
        'created_by' => $user->id,
        'created_at' => now()->subDays(2),
    ]);

    StockMovement::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'quantity' => 5,
        'source_type' => Purchase::class,
        'source_id' => $purchase->id,
        'created_by' => $user->id,
        'created_at' => now()->subDay(),
    ]);

    // Create some existing layers (should be deleted)
    InventoryLayer::factory()->forProductStore($product, $store)->create([
        'unit_cost' => 999,
        'remaining_qty' => 999,
    ]);

    // Run the job
    $job = new RebuildInventoryLayersJob(storeId: $store->id, productId: $product->id);
    $job->handle();

    // Verify layers were rebuilt
    $layers = InventoryLayer::query()
        ->where('product_id', $product->id)
        ->where('store_id', $store->id)
        ->oldest('received_at')
        ->get();

    expect($layers)->toHaveCount(2)
        ->and((int) $layers->first()->received_qty)->toBe(10)
        ->and((int) $layers->first()->remaining_qty)->toBe(10)
        ->and((int) $layers->last()->received_qty)->toBe(5)
        ->and((int) $layers->last()->remaining_qty)->toBe(5);
});

it('rebuilds layers for specific store only', function (): void {
    $user = User::factory()->create();
    $store1 = Store::factory()->create(['created_by' => $user->id]);
    $store2 = Store::factory()->create(['created_by' => $user->id]);
    $product = Product::factory()->create(['created_by' => $user->id, 'cost' => 100]);

    // Create movements in both stores
    StockMovement::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store1->id,
        'quantity' => 10,
        'created_by' => $user->id,
    ]);

    StockMovement::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store2->id,
        'quantity' => 5,
        'created_by' => $user->id,
    ]);

    // Run the job for store1 only
    $job = new RebuildInventoryLayersJob(storeId: $store1->id);
    $job->handle();

    // Verify only store1 has layers
    $layers1 = InventoryLayer::query()->where('store_id', $store1->id)->count();
    $layers2 = InventoryLayer::query()->where('store_id', $store2->id)->count();

    expect($layers1)->toBe(1)
        ->and($layers2)->toBe(0);
});

it('rebuilds layers for specific product only', function (): void {
    $user = User::factory()->create();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $product1 = Product::factory()->create(['created_by' => $user->id, 'cost' => 100]);
    $product2 = Product::factory()->create(['created_by' => $user->id, 'cost' => 200]);

    // Create movements for both products
    StockMovement::factory()->create([
        'product_id' => $product1->id,
        'store_id' => $store->id,
        'quantity' => 10,
        'created_by' => $user->id,
    ]);

    StockMovement::factory()->create([
        'product_id' => $product2->id,
        'store_id' => $store->id,
        'quantity' => 5,
        'created_by' => $user->id,
    ]);

    // Run the job for product1 only
    $job = new RebuildInventoryLayersJob(storeId: null, productId: $product1->id);
    $job->handle();

    // Verify only product1 has layers
    $layers1 = InventoryLayer::query()->where('product_id', $product1->id)->count();
    $layers2 = InventoryLayer::query()->where('product_id', $product2->id)->count();

    expect($layers1)->toBe(1)
        ->and($layers2)->toBe(0);
});

it('ignores outgoing movements when rebuilding layers', function (): void {
    $user = User::factory()->create();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $product = Product::factory()->create(['created_by' => $user->id, 'cost' => 100]);

    // Create incoming and outgoing movements
    StockMovement::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'quantity' => 10, // incoming
        'created_by' => $user->id,
    ]);

    StockMovement::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'quantity' => -5, // outgoing
        'created_by' => $user->id,
    ]);

    // Run the job
    $job = new RebuildInventoryLayersJob(storeId: $store->id, productId: $product->id);
    $job->handle();

    // Verify only incoming movement created a layer
    $layers = InventoryLayer::query()
        ->where('product_id', $product->id)
        ->where('store_id', $store->id)
        ->get();

    expect($layers)->toHaveCount(1)
        ->and((int) $layers->first()->received_qty)->toBe(10);
});

it('processes all stock movements in chronological order', function (): void {
    $user = User::factory()->create();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $product = Product::factory()->create(['created_by' => $user->id, 'cost' => 100]);

    // Create stock movements in chronological order
    $movement1 = StockMovement::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'quantity' => 10,
        'created_by' => $user->id,
        'created_at' => now()->subDays(2),
    ]);

    $movement2 = StockMovement::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'quantity' => 20,
        'created_by' => $user->id,
        'created_at' => now()->subDays(1),
    ]);

    $movement3 = StockMovement::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'quantity' => 30,
        'created_by' => $user->id,
        'created_at' => now(),
    ]);

    // Run the job
    $job = new RebuildInventoryLayersJob(storeId: $store->id, productId: $product->id);
    $job->handle();

    // Verify layers were rebuilt in chronological order
    $layers = InventoryLayer::query()
        ->where('product_id', $product->id)
        ->where('store_id', $store->id)
        ->orderBy('id')
        ->get();

    expect($layers)->toHaveCount(3)
        ->and((int) $layers[0]->received_qty)->toBe(10)
        ->and($layers[0]->received_at->toDateTimeString())->toBe($movement1->created_at->toDateTimeString())
        ->and((int) $layers[1]->received_qty)->toBe(20)
        ->and($layers[1]->received_at->toDateTimeString())->toBe($movement2->created_at->toDateTimeString())
        ->and((int) $layers[2]->received_qty)->toBe(30)
        ->and($layers[2]->received_at->toDateTimeString())->toBe($movement3->created_at->toDateTimeString());

});
