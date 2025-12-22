<?php

declare(strict_types=1);

use App\Actions\Inventory\TransferStock;
use App\Models\InventoryLayer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;

it('may transfer stock between stores', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $fromStore = Store::factory()->create(['created_by' => $user->id]);
    $toStore = Store::factory()->create(['created_by' => $user->id]);

    // Create stock in source store
    InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $fromStore->id,
        'received_qty' => 100,
        'remaining_qty' => 100,
    ]);

    $action = resolve(TransferStock::class);

    $result = $action->handle(
        product: $product,
        fromStore: $fromStore,
        toStore: $toStore,
        quantity: 40,
        batchNumber: null,
        notes: 'Direct transfer',
        userId: $user->id
    );

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('from')
        ->and($result)->toHaveKey('to')
        ->and($result['from']->quantity)->toBe(-40)
        ->and($result['to']->quantity)->toBe(40);

    // Check source store was deducted
    $sourceLayer = InventoryLayer::query()
        ->where('product_id', $product->id)
        ->where('store_id', $fromStore->id)
        ->first();
    expect($sourceLayer->remaining_qty)->toBe(60);

    // Check destination store received stock
    $destLayer = InventoryLayer::query()
        ->where('product_id', $product->id)
        ->where('store_id', $toStore->id)
        ->first();
    expect($destLayer)->not->toBeNull()
        ->and($destLayer->remaining_qty)->toBe(40);
});

it('throws exception for negative quantity', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $fromStore = Store::factory()->create(['created_by' => $user->id]);
    $toStore = Store::factory()->create(['created_by' => $user->id]);

    $action = resolve(TransferStock::class);

    $action->handle(
        product: $product,
        fromStore: $fromStore,
        toStore: $toStore,
        quantity: -10,
        batchNumber: null,
        notes: null,
        userId: $user->id
    );
})->throws(InvalidArgumentException::class, 'Transfer quantity must be positive');

it('throws exception when transferring to same store', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);

    $action = resolve(TransferStock::class);

    $action->handle(
        product: $product,
        fromStore: $store,
        toStore: $store,
        quantity: 10,
        batchNumber: null,
        notes: null,
        userId: $user->id
    );
})->throws(InvalidArgumentException::class, 'Cannot transfer to the same store');

it('may transfer with specific batch number', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $fromStore = Store::factory()->create(['created_by' => $user->id]);
    $toStore = Store::factory()->create(['created_by' => $user->id]);

    InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $fromStore->id,
        'batch_number' => 'BATCH-X',
        'received_qty' => 50,
        'remaining_qty' => 50,
    ]);

    $action = resolve(TransferStock::class);

    $result = $action->handle(
        product: $product,
        fromStore: $fromStore,
        toStore: $toStore,
        quantity: 20,
        batchNumber: 'BATCH-X',
        notes: null,
        userId: $user->id
    );

    expect($result['from']->batch_number)->toBe('BATCH-X')
        ->and($result['to']->batch_number)->toBe('BATCH-X');
});
