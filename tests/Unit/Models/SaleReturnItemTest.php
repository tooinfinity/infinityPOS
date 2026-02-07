<?php

declare(strict_types=1);

use App\Models\SaleReturnItem;

test('to array', function (): void {
    $saleReturnItem = SaleReturnItem::factory()->create()->refresh();

    expect(array_keys($saleReturnItem->toArray()))
        ->toBe([
            'id',
            'sale_return_id',
            'product_id',
            'batch_id',
            'quantity',
            'unit_price',
            'subtotal',
            'created_at',
            'updated_at',
        ]);
});
