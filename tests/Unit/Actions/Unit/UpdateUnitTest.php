<?php

declare(strict_types=1);

use App\Actions\Unit\UpdateUnit;
use App\Data\Unit\UnitData;
use App\Models\Unit;

it('may update a unit name', function (): void {
    $unit = Unit::factory()->create([
        'name' => 'Old Name',
        'short_name' => 'old',
    ]);

    $action = resolve(UpdateUnit::class);

    $data = new UnitData(
        name: 'New Name',
        short_name: $unit->short_name,
        is_active: $unit->is_active,
    );

    $updatedUnit = $action->handle($unit, $data);

    expect($updatedUnit->name)->toBe('New Name')
        ->and($updatedUnit->short_name)->toBe('old');
});

it('may update a unit short_name', function (): void {
    $unit = Unit::factory()->create([
        'name' => 'Kilogram',
        'short_name' => 'kg',
    ]);

    $action = resolve(UpdateUnit::class);

    $data = new UnitData(
        name: $unit->name,
        short_name: 'KG',
        is_active: $unit->is_active,
    );

    $updatedUnit = $action->handle($unit, $data);

    expect($updatedUnit->short_name)->toBe('KG')
        ->and($updatedUnit->name)->toBe('Kilogram');
});

it('updates both name and short_name', function (): void {
    $unit = Unit::factory()->create([
        'name' => 'Old Unit',
        'short_name' => 'ou',
    ]);

    $action = resolve(UpdateUnit::class);

    $data = new UnitData(
        name: 'New Unit',
        short_name: 'nu',
        is_active: $unit->is_active,
    );

    $updatedUnit = $action->handle($unit, $data);

    expect($updatedUnit->name)->toBe('New Unit')
        ->and($updatedUnit->short_name)->toBe('nu');
});

it('updates is_active status', function (): void {
    $unit = Unit::factory()->create([
        'is_active' => true,
    ]);

    $action = resolve(UpdateUnit::class);

    $data = new UnitData(
        name: $unit->name,
        short_name: $unit->short_name,
        is_active: false,
    );

    $updatedUnit = $action->handle($unit, $data);

    expect($updatedUnit->is_active)->toBeFalse();
});

it('activates inactive unit', function (): void {
    $unit = Unit::factory()->create([
        'is_active' => false,
    ]);

    $action = resolve(UpdateUnit::class);

    $data = new UnitData(
        name: $unit->name,
        short_name: $unit->short_name,
        is_active: true,
    );

    $updatedUnit = $action->handle($unit, $data);

    expect($updatedUnit->is_active)->toBeTrue();
});
