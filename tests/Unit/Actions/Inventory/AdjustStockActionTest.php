<?php

declare(strict_types=1);

use App\Actions\Inventory\AdjustStock;
use App\Data\Inventory\AdjustStockData;
use App\Models\InventoryLayer;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;

it('may increase stock with positive adjustment', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);
    $action = resolve(AdjustStock::class);

    $data = AdjustStockData::from([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'quantity' => 50,
        'batch_number' => 'ADJ-001',
        'reason' => 'Stock found',
        'notes' => 'Found in warehouse',
        'created_by' => $user->id,
    ]);

    $movement = $action->handle($data);

    expect($movement)->toBeInstanceOf(StockMovement::class)
        ->and($movement->quantity)->toBe(50)
        ->and($movement->product_id)->toBe($product->id)
        ->and($movement->store_id)->toBe($store->id)
        ->and($movement->created_by)->toBe($user->id);

    // Check layer was created
    $layer = InventoryLayer::query()
        ->where('product_id', $product->id)
        ->where('store_id', $store->id)
        ->where('batch_number', 'ADJ-001')
        ->first();

    expect($layer)->not->toBeNull()
        ->and($layer->remaining_qty)->toBe(50);
});

it('may decrease stock with negative adjustment', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);

    // Create existing stock
    InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'received_qty' => 100,
        'remaining_qty' => 100,
    ]);

    $action = resolve(AdjustStock::class);

    $data = AdjustStockData::from([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'quantity' => -30,
        'batch_number' => null,
        'reason' => 'Damaged goods',
        'notes' => 'Damaged during transport',
        'created_by' => $user->id,
    ]);

    $movement = $action->handle($data);

    expect($movement->quantity)->toBe(-30);

    // Check layer was deducted
    $layer = InventoryLayer::query()
        ->where('product_id', $product->id)
        ->where('store_id', $store->id)
        ->first();

    expect($layer->remaining_qty)->toBe(70);
});

it('throws exception for zero adjustment', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);
    $action = resolve(AdjustStock::class);

    $data = AdjustStockData::from([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'quantity' => 0,
        'batch_number' => null,
        'reason' => null,
        'notes' => null,
        'created_by' => $user->id,
    ]);

    $action->handle($data);
})->throws(InvalidArgumentException::class, 'Adjustment quantity cannot be zero');

it('creates movement with proper notes', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);
    $action = resolve(AdjustStock::class);

    $data = AdjustStockData::from([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'quantity' => 10,
        'batch_number' => null,
        'reason' => 'Stock count',
        'notes' => 'Annual inventory',
        'created_by' => $user->id,
    ]);

    $movement = $action->handle($data);

    expect($movement->notes)->toContain('Stock adjustment')
        ->and($movement->notes)->toContain('Stock count')
        ->and($movement->notes)->toContain('Annual inventory');
});
