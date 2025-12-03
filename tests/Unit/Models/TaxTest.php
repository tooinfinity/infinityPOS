<?php

declare(strict_types=1);

use App\Models\Tax;

test('to array', function (): void {
    $tax = Tax::factory()->create()->refresh();

    expect(array_keys($tax->toArray()))
        ->toBe([
            'id',
            'name',
            'tax_type',
            'rate',
            'is_active',
            'created_at',
            'updated_at',
        ]);
});
