<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\Store;
use App\Models\StoreStock;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $product = Product::factory()->create(['created_by' => $user->id]);

    $storeStock = StoreStock::factory()->create([
        'store_id' => $store->id,
        'product_id' => $product->id,
    ]);

    expect(array_keys($storeStock->toArray()))
        ->toBe([
            'store_id',
            'product_id',
            'quantity',
            'updated_at',
            'created_at',
        ]);
});

test('store stock relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $product = Product::factory()->create(['created_by' => $user->id]);

    $storeStock = StoreStock::factory()->create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'quantity' => 7,
    ]);

    expect($storeStock->store()->first()->id)->toBe($store->id)
        ->and($storeStock->product()->withoutGlobalScopes()->first()->id)->toBe($product->id)
        ->and((int) $storeStock->quantity)->toBe(7);
});

test('store stock factory states', function (): void {
    $user = User::factory()->create()->refresh();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $product = Product::factory()->create(['created_by' => $user->id]);

    $withStock = StoreStock::factory()->withStock(12)->create([
        'store_id' => $store->id,
        'product_id' => $product->id,
    ]);

    $anotherProduct = Product::factory()->create(['created_by' => $user->id]);

    $empty = StoreStock::factory()->empty()->create([
        'store_id' => $store->id,
        'product_id' => $anotherProduct->id,
    ]);

    expect((int) $withStock->quantity)->toBe(12)
        ->and((int) $empty->quantity)->toBe(0);
});
