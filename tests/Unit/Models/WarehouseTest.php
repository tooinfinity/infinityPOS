<?php

declare(strict_types=1);

use App\Models\Warehouse;

test('to array', function (): void {
    $warehouse = Warehouse::factory()->create()->refresh();

    expect(array_keys($warehouse->toArray()))
        ->toBe([
            'id',
            'name',
            'code',
            'email',
            'phone',
            'address',
            'city',
            'country',
            'is_active',
            'created_at',
            'updated_at',
        ]);
});

test('only returns active warehouses by default', function (): void {
    Warehouse::factory()->count(2)->create([
        'is_active' => true,
    ]);
    Warehouse::factory()->count(2)->create([
        'is_active' => false,
    ]);

    $warehouses = Warehouse::all();

    expect($warehouses)
        ->toHaveCount(2);
});
