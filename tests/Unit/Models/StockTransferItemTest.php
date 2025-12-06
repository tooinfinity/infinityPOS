<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Store;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $fromStore = Store::factory()->create(['created_by' => $user->id]);
    $toStore = Store::factory()->create(['created_by' => $user->id]);

    $stockTransfer = StockTransfer::factory()->create([
        'created_by' => $user->id,
        'from_store_id' => $fromStore->id,
        'to_store_id' => $toStore->id,
    ])->refresh();
    $stockTransferItem = StockTransferItem::factory()->create(['stock_transfer_id' => $stockTransfer->id, 'product_id' => $product->id]);

    expect(array_keys($stockTransferItem->toArray()))
        ->toBe([
            'stock_transfer_id',
            'product_id',
            'quantity',
            'batch_number',
            'updated_at',
            'created_at',
            'id',
        ]);
});

test('stock transfer relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $fromStore = Store::factory()->create(['created_by' => $user->id]);
    $toStore = Store::factory()->create(['created_by' => $user->id]);

    $stockTransfer = StockTransfer::factory()->create([
        'created_by' => $user->id,
        'from_store_id' => $fromStore->id,
        'to_store_id' => $toStore->id,
    ])->refresh();

    $stockTransferItem = StockTransferItem::factory()->create([
        'stock_transfer_id' => $stockTransfer->id,
        'product_id' => $product->id,
    ]);

    expect($stockTransferItem->stockTransfer->id)->toBe($stockTransfer->id)
        ->and($stockTransferItem->product->id)->toBe($product->id)
        ->and($stockTransferItem->stockTransfer->fromStore->id)->toBe($fromStore->id)
        ->and($stockTransferItem->stockTransfer->toStore->id)->toBe($toStore->id);
});
