<?php

declare(strict_types=1);

use App\Models\PurchaseReturnItem;

test('to array', function (): void {
    $purchaseReturnItem = PurchaseReturnItem::factory()->create()->refresh();

    expect(array_keys($purchaseReturnItem->toArray()))
        ->toBe([
            'id',
            'purchase_return_id',
            'product_id',
            'batch_id',
            'quantity',
            'unit_cost',
            'subtotal',
            'created_at',
            'updated_at',
        ]);
});
