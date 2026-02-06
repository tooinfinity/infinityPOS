<?php

declare(strict_types=1);

use App\Models\Sale;

test('to array', function (): void {
    $sale = Sale::factory()->create()->refresh();

    expect(array_keys($sale->toArray()))
        ->toBe([
            'id',
            'customer_id',
            'warehouse_id',
            'user_id',
            'reference_no',
            'status',
            'sale_date',
            'total_amount',
            'paid_amount',
            'change_amount',
            'payment_status',
            'note',
            'created_at',
            'updated_at',
        ]);
});
