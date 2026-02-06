<?php

declare(strict_types=1);

use App\Models\StockTransferItem;

test('to array', function (): void {
    $stockTransferItem = StockTransferItem::factory()->create()->refresh();

    expect(array_keys($stockTransferItem->toArray()))
        ->toBe([
            'id',
            'stock_transfer_id',
            'product_id',
            'batch_id',
            'quantity',
            'created_at',
            'updated_at',
        ]);
});
