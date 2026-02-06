<?php

declare(strict_types=1);

use App\Models\Supplier;

test('to array', function (): void {
    $supplier = Supplier::factory()->create()->refresh();

    expect(array_keys($supplier->toArray()))
        ->toBe([
            'id',
            'name',
            'company_name',
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

test('only returns active suppliers by default', function (): void {
    Supplier::factory()->count(2)->create([
        'is_active' => true,
    ]);
    Supplier::factory()->count(2)->create([
        'is_active' => false,
    ]);

    $suppliers = Supplier::all();

    expect($suppliers)
        ->toHaveCount(2);
});
