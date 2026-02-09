<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\Unit;

test('to array', function (): void {
    $unit = Unit::factory()->create()->refresh();

    expect(array_keys($unit->toArray()))
        ->toBe([
            'id',
            'name',
            'short_name',
            'is_active',
            'created_at',
            'updated_at',
        ]);
});

test('only returns active units by default', function (): void {
    Unit::factory()->count(2)->create([
        'is_active' => true,
    ]);
    Unit::factory()->count(2)->create([
        'is_active' => false,
    ]);

    $units = Unit::all();

    expect($units)
        ->toHaveCount(2);
});

test('unit has many products', function (): void {
    $unit = Unit::factory()->create()->refresh();
    Product::factory()->create([
        'unit_id' => $unit->id,
    ]);

    expect($unit->products)->toHaveCount(1);
});
