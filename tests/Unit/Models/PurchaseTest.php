<?php

declare(strict_types=1);

use App\Models\Purchase;

test('to array', function (): void {
    $purchase = Purchase::factory()->create()->refresh();

    expect(array_keys($purchase->toArray()))
        ->toBe([
            'id',
            'supplier_id',
            'warehouse_id',
            'user_id',
            'reference_no',
            'status',
            'purchase_date',
            'total_amount',
            'paid_amount',
            'payment_status',
            'note',
            'document',
            'created_at',
            'updated_at',
        ]);
});
