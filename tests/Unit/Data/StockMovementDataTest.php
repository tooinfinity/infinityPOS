<?php

declare(strict_types=1);

use App\Data\Products\ProductData;
use App\Data\StockMovementData;
use App\Data\Stores\StoreData;
use App\Data\Users\UserData;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;

it('transforms a stock movement model into StockMovementData', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();
    $store = Store::factory()->create();
    $purchase = Purchase::factory()->create();
    $product = Product::factory()->create();

    /** @var StockMovement $movement */
    $movement = StockMovement::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->for($store, 'store')
        ->for($product, 'product')
        ->create([
            'quantity' => 10,
            'source_type' => Purchase::class,
            'source_id' => $purchase->id,
            'batch_number' => 'BATCH-1',
            'notes' => 'Initial stock',
        ]);

    $data = StockMovementData::from(
        $movement->load(['creator', 'updater', 'store', 'product'])
    );

    expect($data)
        ->toBeInstanceOf(StockMovementData::class)
        ->id->toBe($movement->id)
        ->quantity->toBe(10)
        ->source_type->toBe(Purchase::class)
        ->source_id->toBe($purchase->id)
        ->batch_number->toBe('BATCH-1')
        ->notes->toBe('Initial stock')
        ->and($data->store->resolve())
        ->toBeInstanceOf(StoreData::class)
        ->id->toBe($store->id)
        ->and($data->product->resolve())
        ->toBeInstanceOf(ProductData::class)
        ->id->toBe($product->id)
        ->and($data->creator->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($creator->id)
        ->and($data->updater->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($updater->id)
        ->and($data->created_at)
        ->toBe($movement->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($movement->updated_at->toDateTimeString());
});
