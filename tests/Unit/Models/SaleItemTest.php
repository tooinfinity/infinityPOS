<?php

declare(strict_types=1);

use App\Models\SaleItem;

test('to array', function (): void {
    $saleItem = SaleItem::factory()->create()->refresh();

    expect(array_keys($saleItem->toArray()))
        ->toBe([
            'id',
            'sale_id',
            'product_id',
            'batch_id',
            'quantity',
            'unit_price',
            'unit_cost',
            'subtotal',
            'created_at',
            'updated_at',
        ]);
});
