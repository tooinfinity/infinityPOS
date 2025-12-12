<?php

declare(strict_types=1);

use App\Data\ProductData;
use App\Data\StockTransferData;
use App\Data\StockTransferItemData;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;

it('transforms a stock transfer item model into StockTransferItemData', function (): void {
    $transfer = StockTransfer::factory()->create();
    $product = Product::factory()->create();

    /** @var StockTransferItem $item */
    $item = StockTransferItem::factory()
        ->for($transfer, 'stockTransfer')
        ->for($product, 'product')
        ->create([
            'quantity' => 7,
            'batch_number' => 'B-777',
        ]);

    $data = StockTransferItemData::from(
        $item->load(['stockTransfer', 'product'])
    );

    expect($data)
        ->toBeInstanceOf(StockTransferItemData::class)
        ->id->toBe($item->id)
        ->quantity->toBe(7)
        ->batch_number->toBe('B-777')
        ->and($data->stockTransfer->resolve())
        ->toBeInstanceOf(StockTransferData::class)
        ->id->toBe($transfer->id)
        ->and($data->product->resolve())
        ->toBeInstanceOf(ProductData::class)
        ->id->toBe($product->id)
        ->and($data->created_at)
        ->toBe($item->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($item->updated_at->toDateTimeString());
});
