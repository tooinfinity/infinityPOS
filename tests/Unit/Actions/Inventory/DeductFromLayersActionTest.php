<?php

declare(strict_types=1);

use App\Actions\Inventory\DeductFromLayers;
use App\Models\InventoryLayer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;

it('may deduct quantity from single layer', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);

    InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'received_qty' => 100,
        'remaining_qty' => 100,
        'received_at' => now()->subDays(5),
    ]);

    $action = resolve(DeductFromLayers::class);

    $action->handle($product, $store, 30);

    $layer = InventoryLayer::query()
        ->where('product_id', $product->id)
        ->where('store_id', $store->id)
        ->first();

    expect($layer->remaining_qty)->toBe(70);
});

it('may deduct quantity using FIFO from multiple layers', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);

    // Oldest layer
    $layer1 = InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'received_qty' => 50,
        'remaining_qty' => 50,
        'received_at' => now()->subDays(10),
    ]);

    // Newer layer
    $layer2 = InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'received_qty' => 50,
        'remaining_qty' => 50,
        'received_at' => now()->subDays(5),
    ]);

    $action = resolve(DeductFromLayers::class);

    // Deduct 70 units (50 from layer1, 20 from layer2)
    $action->handle($product, $store, 70);

    $layer1->refresh();
    $layer2->refresh();

    expect($layer1->remaining_qty)->toBe(0)
        ->and($layer2->remaining_qty)->toBe(30);
});

it('may deduct quantity from specific batch', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);

    InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'batch_number' => 'BATCH-A',
        'received_qty' => 50,
        'remaining_qty' => 50,
    ]);

    InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'batch_number' => 'BATCH-B',
        'received_qty' => 50,
        'remaining_qty' => 50,
    ]);

    $action = resolve(DeductFromLayers::class);

    $action->handle($product, $store, 20, 'BATCH-B');

    $layerA = InventoryLayer::query()->where('batch_number', 'BATCH-A')->first();
    $layerB = InventoryLayer::query()->where('batch_number', 'BATCH-B')->first();

    expect($layerA->remaining_qty)->toBe(50)
        ->and($layerB->remaining_qty)->toBe(30);
});

it('throws exception when insufficient stock', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);

    InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'received_qty' => 50,
        'remaining_qty' => 30,
    ]);

    $action = resolve(DeductFromLayers::class);

    $action->handle($product, $store, 50);
})->throws(InvalidArgumentException::class, 'Insufficient stock');

it('throws exception for negative quantity', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);

    $action = resolve(DeductFromLayers::class);

    $action->handle($product, $store, -10);
})->throws(InvalidArgumentException::class, 'Quantity must be positive');
