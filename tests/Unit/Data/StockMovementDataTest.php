<?php

declare(strict_types=1);

use App\Data\ProductData;
use App\Data\StockMovementData;
use App\Data\StoreData;
use App\Data\UserData;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;

it('transforms a stock movement model into StockMovementData', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();
    $store = Store::factory()->create();
    $product = Product::factory()->create();

    /** @var StockMovement $movement */
    $movement = StockMovement::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->for($store, 'store')
        ->for($product, 'product')
        ->create([
            'quantity' => 10,
            'type' => App\Enums\StockMovementTypeEnum::PURCHASE->value,
            'reference' => 'REF-100',
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
        ->type->toBe(App\Enums\StockMovementTypeEnum::PURCHASE)
        ->reference->toBe('REF-100')
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
