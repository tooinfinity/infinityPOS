<?php

declare(strict_types=1);

use App\Models\InventoryLayer;
use App\Models\Product;
use App\Models\Store;

it('to array', function (): void {
    $product = Product::factory()->create();
    $store = Store::factory()->create(['created_by' => $product->created_by]);

    $layer = InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
    ])->refresh();

    expect(array_keys($layer->toArray()))
        ->toBe([
            'id',
            'product_id',
            'store_id',
            'batch_number',
            'expiry_date',
            'unit_cost',
            'received_qty',
            'remaining_qty',
            'received_at',
            'created_at',
            'updated_at',
        ]);
});

it('relationships', function (): void {
    $product = Product::factory()->create();
    $store = Store::factory()->create(['created_by' => $product->created_by]);

    $layer = InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
    ])->refresh();

    expect($layer->product->id)->toBe($product->id)
        ->and($layer->store->id)->toBe($store->id);
});
