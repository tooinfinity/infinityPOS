<?php

declare(strict_types=1);

use App\Data\ProductData;
use App\Data\StoreData;
use App\Data\StoreStockData;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreStock;

it('transforms a store stock pivot into StoreStockData', function (): void {
    $store = Store::factory()->create();
    $product = Product::factory()->create();

    /** @var StoreStock $storeStock */
    $storeStock = StoreStock::factory()->create([
        'store_id' => $store->id,
        'product_id' => $product->id,
        'quantity' => 42,
    ]);

    $data = StoreStockData::from(
        $storeStock->load(['store', 'product'])
    );

    expect($data)
        ->toBeInstanceOf(StoreStockData::class)
        ->quantity->toBe(42)
        ->and($data->store->resolve())
        ->toBeInstanceOf(StoreData::class)
        ->id->toBe($store->id)
        ->and($data->product->resolve())
        ->toBeInstanceOf(ProductData::class)
        ->id->toBe($product->id)
        ->and($data->created_at)
        ->toBe($storeStock->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($storeStock->updated_at->toDateTimeString());
});
