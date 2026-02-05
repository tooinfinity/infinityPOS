<?php

declare(strict_types=1);

use App\Models\StockTransfer;

test('to array', function (): void {
    $stockTransfer = StockTransfer::factory()->create()->refresh();

    expect(array_keys($stockTransfer->toArray()))
        ->toBe([
            'id',
            'from_warehouse_id',
            'to_warehouse_id',
            'user_id',
            'reference_no',
            'status',
            'note',
            'transfer_date',
            'created_at',
            'updated_at',
        ]);
});
