<?php

declare(strict_types=1);

use App\Actions\Inventory\CreateInventoryLayer;
use App\Data\Inventory\CreateInventoryLayerData;
use App\Models\InventoryLayer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;

it('may create an inventory layer', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);
    $action = resolve(CreateInventoryLayer::class);

    $data = CreateInventoryLayerData::from([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'batch_number' => 'BATCH-001',
        'expiry_date' => now()->addYear()->toDateString(),
        'unit_cost' => 5000,
        'received_qty' => 100,
        'remaining_qty' => 100,
        'received_at' => now()->toDateTimeString(),
    ]);

    $layer = $action->handle($data);

    expect($layer)->toBeInstanceOf(InventoryLayer::class)
        ->and($layer->product_id)->toBe($product->id)
        ->and($layer->store_id)->toBe($store->id)
        ->and($layer->batch_number)->toBe('BATCH-001')
        ->and($layer->unit_cost)->toBe(5000)
        ->and($layer->received_qty)->toBe(100)
        ->and($layer->remaining_qty)->toBe(100);
});

it('may create an inventory layer without batch', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);
    $action = resolve(CreateInventoryLayer::class);

    $data = CreateInventoryLayerData::from([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'batch_number' => null,
        'expiry_date' => null,
        'unit_cost' => 3000,
        'received_qty' => 50,
        'remaining_qty' => 50,
        'received_at' => now()->toDateTimeString(),
    ]);

    $layer = $action->handle($data);

    expect($layer->batch_number)->toBeNull()
        ->and($layer->expiry_date)->toBeNull();
});
