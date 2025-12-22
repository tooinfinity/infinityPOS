<?php

declare(strict_types=1);

use App\Actions\Inventory\CreateStockMovement;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;

it('may create a stock movement', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);
    $action = resolve(CreateStockMovement::class);

    $movement = $action->handle(
        productId: $product->id,
        storeId: $store->id,
        quantity: 50,
        sourceType: null,
        sourceId: null,
        batchNumber: 'BATCH-001',
        notes: 'Manual adjustment',
        createdBy: $user->id
    );

    expect($movement)->toBeInstanceOf(StockMovement::class)
        ->and($movement->product_id)->toBe($product->id)
        ->and($movement->store_id)->toBe($store->id)
        ->and($movement->quantity)->toBe(50)
        ->and($movement->batch_number)->toBe('BATCH-001')
        ->and($movement->notes)->toBe('Manual adjustment')
        ->and($movement->created_by)->toBe($user->id);
});

it('may create movement with source', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);
    $purchase = Purchase::factory()->create(['created_by' => $user->id]);
    $action = resolve(CreateStockMovement::class);

    $movement = $action->handle(
        productId: $product->id,
        storeId: $store->id,
        quantity: 100,
        sourceType: Purchase::class,
        sourceId: $purchase->id,
        batchNumber: null,
        notes: 'Purchase received',
        createdBy: $user->id
    );

    expect($movement->source_type)->toBe(Purchase::class)
        ->and($movement->source_id)->toBe($purchase->id);
});

it('may create movement without optional fields', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);
    $action = resolve(CreateStockMovement::class);

    $movement = $action->handle(
        productId: $product->id,
        storeId: $store->id,
        quantity: -25
    );

    expect($movement->quantity)->toBe(-25)
        ->and($movement->batch_number)->toBeNull()
        ->and($movement->notes)->toBeNull()
        ->and($movement->created_by)->toBeNull();
});
