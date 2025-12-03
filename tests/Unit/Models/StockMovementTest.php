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
            'type',
            'reference',
            'batch_number',
            'notes',
            'created_by',
            'updated_by',
            'product_id',
            'store_id',
            'created_at',
            'updated_at',
        ]);
});
