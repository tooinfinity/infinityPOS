<?php

declare(strict_types=1);

use App\Actions\Inventory\RecalculateStockLevels;
use App\Models\InventoryLayer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;

it('may recalculate total stock from layers', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);

    InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'remaining_qty' => 50,
    ]);

    InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'remaining_qty' => 30,
    ]);

    InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'remaining_qty' => 20,
    ]);

    $action = resolve(RecalculateStockLevels::class);

    $total = $action->handle($product, $store);

    expect($total)->toBe(100);
});

it('returns zero when no layers exist', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);

    $action = resolve(RecalculateStockLevels::class);

    $total = $action->handle($product, $store);

    expect($total)->toBe(0);
});
