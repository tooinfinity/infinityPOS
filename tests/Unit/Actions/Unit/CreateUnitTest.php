<?php

declare(strict_types=1);

use App\Actions\Unit\CreateUnit;
use App\Data\Unit\CreateUnitData;
use App\Models\Unit;

it('may create a unit', function (): void {
    $action = resolve(CreateUnit::class);

    $data = new CreateUnitData(
        name: 'Kilogram',
        short_name: 'kg',
        is_active: true,
    );

    $unit = $action->handle($data);

    expect($unit)->toBeInstanceOf(Unit::class)
        ->and($unit->name)->toBe('Kilogram')
        ->and($unit->short_name)->toBe('kg')
        ->and($unit->exists)->toBeTrue();
});

it('creates unit with is_active flag', function (): void {
    $action = resolve(CreateUnit::class);

    $data = new CreateUnitData(
        name: 'Piece',
        short_name: 'pc',
        is_active: false,
    );

    $unit = $action->handle($data);

    expect($unit->is_active)->toBeFalse();
});

it('defaults is_active to true when not provided', function (): void {
    $action = resolve(CreateUnit::class);

    $data = new CreateUnitData(
        name: 'Liter',
        short_name: 'l',
        is_active: true,
    );

    $unit = $action->handle($data);

    expect($unit->is_active)->toBeTrue();
});

it('creates unit with various names', function (): void {
    $action = resolve(CreateUnit::class);

    $data = new CreateUnitData(
        name: 'Box',
        short_name: 'box',
        is_active: true,
    );

    $unit = $action->handle($data);

    expect($unit->name)->toBe('Box')
        ->and($unit->short_name)->toBe('box');
});

it('creates unit with uppercase short name', function (): void {
    $action = resolve(CreateUnit::class);

    $data = new CreateUnitData(
        name: 'Dozen',
        short_name: 'DZ',
        is_active: true,
    );

    $unit = $action->handle($data);

    expect($unit->short_name)->toBe('DZ');
});
