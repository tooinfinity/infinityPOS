<?php

declare(strict_types=1);

use App\Actions\Unit\UpdateUnit;
use App\Models\Unit;

it('may update a unit name', function (): void {
    $unit = Unit::factory()->create([
        'name' => 'Old Name',
        'short_name' => 'old',
    ]);

    $action = resolve(UpdateUnit::class);

    $updatedUnit = $action->handle($unit, [
        'name' => 'New Name',
    ]);

    expect($updatedUnit->name)->toBe('New Name')
        ->and($updatedUnit->short_name)->toBe('old');
});

it('may update a unit short_name', function (): void {
    $unit = Unit::factory()->create([
        'name' => 'Kilogram',
        'short_name' => 'kg',
    ]);

    $action = resolve(UpdateUnit::class);

    $updatedUnit = $action->handle($unit, [
        'short_name' => 'KG',
    ]);

    expect($updatedUnit->short_name)->toBe('KG')
        ->and($updatedUnit->name)->toBe('Kilogram');
});

it('updates both name and short_name', function (): void {
    $unit = Unit::factory()->create([
        'name' => 'Old Unit',
        'short_name' => 'ou',
    ]);

    $action = resolve(UpdateUnit::class);

    $updatedUnit = $action->handle($unit, [
        'name' => 'New Unit',
        'short_name' => 'nu',
    ]);

    expect($updatedUnit->name)->toBe('New Unit')
        ->and($updatedUnit->short_name)->toBe('nu');
});

it('updates is_active status', function (): void {
    $unit = Unit::factory()->create([
        'is_active' => true,
    ]);

    $action = resolve(UpdateUnit::class);

    $updatedUnit = $action->handle($unit, [
        'is_active' => false,
    ]);

    expect($updatedUnit->is_active)->toBeFalse();
});

it('activates inactive unit', function (): void {
    $unit = Unit::factory()->create([
        'is_active' => false,
    ]);

    $action = resolve(UpdateUnit::class);

    $updatedUnit = $action->handle($unit, [
        'is_active' => true,
    ]);

    expect($updatedUnit->is_active)->toBeTrue();
});
