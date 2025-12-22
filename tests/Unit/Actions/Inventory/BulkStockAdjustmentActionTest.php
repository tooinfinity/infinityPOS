<?php

declare(strict_types=1);

use App\Actions\Inventory\BulkStockAdjustment;
use App\Data\Inventory\AdjustStockData;
use App\Data\Inventory\BulkStockAdjustmentData;
use App\Models\InventoryLayer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;

it('may perform bulk stock adjustments', function (): void {
    $user = User::factory()->create();
    $product1 = Product::factory()->create(['created_by' => $user->id]);
    $product2 = Product::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);

    // Create existing stock for product2
    InventoryLayer::factory()->create([
        'product_id' => $product2->id,
        'store_id' => $store->id,
        'received_qty' => 100,
        'remaining_qty' => 100,
    ]);

    $action = resolve(BulkStockAdjustment::class);

    $data = BulkStockAdjustmentData::from([
        'adjustments' => [
            AdjustStockData::from([
                'product_id' => $product1->id,
                'store_id' => $store->id,
                'quantity' => 50,
                'batch_number' => null,
                'reason' => 'Stock count',
                'notes' => 'Increase',
                'created_by' => $user->id,
            ]),
            AdjustStockData::from([
                'product_id' => $product2->id,
                'store_id' => $store->id,
                'quantity' => -20,
                'batch_number' => null,
                'reason' => 'Damaged',
                'notes' => 'Decrease',
                'created_by' => $user->id,
            ]),
        ],
        'reference' => 'BULK-ADJ-001',
        'notes' => 'Year end adjustment',
    ]);

    $movements = $action->handle($data);

    expect($movements)->toHaveCount(2)
        ->and($movements->first()->quantity)->toBe(50)
        ->and($movements->last()->quantity)->toBe(-20);

    // Check product1 layer created
    $layer1 = InventoryLayer::query()
        ->where('product_id', $product1->id)
        ->where('store_id', $store->id)
        ->first();
    expect($layer1)->not->toBeNull()
        ->and($layer1->remaining_qty)->toBe(50);

    // Check product2 layer deducted
    $layer2 = InventoryLayer::query()
        ->where('product_id', $product2->id)
        ->where('store_id', $store->id)
        ->first();
    expect($layer2->remaining_qty)->toBe(80);
});

it('rolls back all adjustments on error', function (): void {
    $user = User::factory()->create();
    $product1 = Product::factory()->create(['created_by' => $user->id]);
    $product2 = Product::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);

    // Create insufficient stock for product2
    InventoryLayer::factory()->create([
        'product_id' => $product2->id,
        'store_id' => $store->id,
        'received_qty' => 10,
        'remaining_qty' => 10,
    ]);

    $action = resolve(BulkStockAdjustment::class);

    $data = BulkStockAdjustmentData::from([
        'adjustments' => [
            AdjustStockData::from([
                'product_id' => $product1->id,
                'store_id' => $store->id,
                'quantity' => 50,
                'batch_number' => null,
                'reason' => null,
                'notes' => null,
                'created_by' => $user->id,
            ]),
            AdjustStockData::from([
                'product_id' => $product2->id,
                'store_id' => $store->id,
                'quantity' => -50, // More than available
                'batch_number' => null,
                'reason' => null,
                'notes' => null,
                'created_by' => $user->id,
            ]),
        ],
        'reference' => 'BULK-ADJ-002',
        'notes' => null,
    ]);

    try {
        $action->handle($data);
    } catch (Exception) {
        // Expected to fail
    }

    // Verify first adjustment was rolled back (no layer created for product1)
    $layer1 = InventoryLayer::query()
        ->where('product_id', $product1->id)
        ->where('store_id', $store->id)
        ->first();
    expect($layer1)->toBeNull();

    // Verify product2 layer unchanged
    $layer2 = InventoryLayer::query()
        ->where('product_id', $product2->id)
        ->where('store_id', $store->id)
        ->first();
    expect($layer2->remaining_qty)->toBe(10);
});
