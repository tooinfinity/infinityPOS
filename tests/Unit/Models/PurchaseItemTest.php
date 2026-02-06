<?php

declare(strict_types=1);

use App\Models\PurchaseItem;

test('to array', function (): void {
    $purchaseItem = PurchaseItem::factory()->create()->refresh();

    expect(array_keys($purchaseItem->toArray()))
        ->toBe([
            'id',
            'purchase_id',
            'product_id',
            'batch_id',
            'quantity',
            'received_quantity',
            'unit_cost',
            'subtotal',
            'expires_at',
            'created_at',
            'updated_at',
        ]);
});
