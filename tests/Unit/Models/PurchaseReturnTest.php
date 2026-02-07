<?php

declare(strict_types=1);

use App\Models\PurchaseReturn;

test('to array', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->create()->refresh();

    expect(array_keys($purchaseReturn->toArray()))
        ->toBe([
            'id',
            'purchase_id',
            'warehouse_id',
            'user_id',
            'reference_no',
            'return_date',
            'total_amount',
            'status',
            'note',
            'created_at',
            'updated_at',
        ]);
});
