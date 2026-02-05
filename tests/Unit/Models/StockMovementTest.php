<?php

declare(strict_types=1);

use App\Models\StockMovement;

test('to array', function (): void {
    $stockMovement = StockMovement::factory()->create()->refresh();

    expect(array_keys($stockMovement->toArray()))
        ->toBe([
            'id',
            'warehouse_id',
            'product_id',
            'batch_id',
            'user_id',
            'type',
            'quantity',
            'previous_quantity',
            'current_quantity',
            'reference_type',
            'reference_id',
            'note',
            'created_at',
        ]);
});
