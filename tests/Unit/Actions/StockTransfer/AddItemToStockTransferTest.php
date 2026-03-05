<?php

declare(strict_types=1);

use App\Actions\StockTransfer\AddItemToStockTransfer;
use App\Data\StockTransfer\StockTransferItemData;
use App\Models\Batch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;

it('may add item to pending transfer', function (): void {
    $transfer = StockTransfer::factory()->pending()->create();
    $product = Product::factory()->create();

    $action = resolve(AddItemToStockTransfer::class);

    $itemData = new StockTransferItemData(
        product_id: $product->id,
        batch_id: null,
        quantity: 15,
    );

    $item = $action->handle($transfer, $itemData);

    expect($item)->toBeInstanceOf(StockTransferItem::class)
        ->and($item->stock_transfer_id)->toBe($transfer->id)
        ->and($item->product_id)->toBe($product->id)
        ->and($item->quantity)->toBe(15);
});

it('may add item with batch to pending transfer', function (): void {
    $transfer = StockTransfer::factory()->pending()->create();
    $batch = Batch::factory()->create();

    $action = resolve(AddItemToStockTransfer::class);

    $itemData = new StockTransferItemData(
        product_id: $batch->product_id,
        batch_id: $batch->id,
        quantity: 20,
    );

    $item = $action->handle($transfer, $itemData);

    expect($item->batch_id)->toBe($batch->id);
});
