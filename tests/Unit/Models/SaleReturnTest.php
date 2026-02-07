<?php

declare(strict_types=1);

use App\Models\SaleReturn;

test('to array', function (): void {
    $saleReturn = SaleReturn::factory()->create()->refresh();

    expect(array_keys($saleReturn->toArray()))
        ->toBe([
            'id',
            'sale_id',
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
