<?php

declare(strict_types=1);

use App\Models\Batch;

test('to array', function (): void {
    $batch = Batch::factory()->create()->refresh();

    expect(array_keys($batch->toArray()))
        ->toBe([
            'id',
            'product_id',
            'batch_number',
            'cost_amount',
            'quantity',
            'expires_at',
            'created_at',
            'updated_at',
        ]);
});
