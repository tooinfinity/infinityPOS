<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $product = Product::factory()->create(['created_by' => $user->id]);

    $stockMovement = StockMovement::factory()->create([
        'created_by' => $user->id,
        'store_id' => $store->id,
        'product_id' => $product->id,
    ])->refresh();

    expect(array_keys($stockMovement->toArray()))
        ->toBe([
            'id',
            'quantity',
            'source_type',
            'source_id',
            'batch_number',
            'notes',
            'product_id',
            'store_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});

test('stock movement relationships and helpers', function (): void {
    $user = User::factory()->create()->refresh();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $product = Product::factory()->create(['created_by' => $user->id]);

    $incoming = StockMovement::factory()->create([
        'created_by' => $user->id,
        'updated_by' => $user->id,
        'store_id' => $store->id,
        'product_id' => $product->id,
        'quantity' => 5,

    ])->refresh();

    $outgoing = StockMovement::factory()->create([
        'created_by' => $user->id,
        'store_id' => $store->id,
        'product_id' => $product->id,
        'quantity' => -2,

    ])->refresh();

    expect($incoming->creator->id)->toBe($user->id)
        ->and($incoming->updater->id)->toBe($user->id)
        ->and($incoming->store->id)->toBe($store->id)
        ->and($incoming->product->id)->toBe($product->id)
        ->and($incoming->isIncoming())->toBeTrue()
        ->and($incoming->isOutgoing())->toBeFalse()
        ->and($outgoing->isIncoming())->toBeFalse()
        ->and($outgoing->isOutgoing())->toBeTrue();
});
