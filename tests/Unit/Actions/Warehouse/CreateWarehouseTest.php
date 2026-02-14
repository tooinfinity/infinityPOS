<?php

declare(strict_types=1);

use App\Actions\Warehouse\CreateWarehouse;
use App\Models\Warehouse;

it('may create a warehouse with required fields', function (): void {
    $action = resolve(CreateWarehouse::class);

    $warehouse = $action->handle([
        'name' => 'Main Warehouse',
        'code' => 'WH-001',
    ]);

    expect($warehouse)->toBeInstanceOf(Warehouse::class)
        ->and($warehouse->name)->toBe('Main Warehouse')
        ->and($warehouse->code)->toBe('WH-001')
        ->and($warehouse->exists)->toBeTrue();
});

it('creates warehouse with all optional fields', function (): void {
    $action = resolve(CreateWarehouse::class);

    $warehouse = $action->handle([
        'name' => 'Central Warehouse',
        'code' => 'WH-CENTRAL',
        'email' => 'warehouse@example.com',
        'phone' => '+1234567890',
        'address' => '123 Warehouse St',
        'city' => 'New York',
        'country' => 'USA',
        'is_active' => false,
    ]);

    expect($warehouse->name)->toBe('Central Warehouse')
        ->and($warehouse->code)->toBe('WH-CENTRAL')
        ->and($warehouse->email)->toBe('warehouse@example.com')
        ->and($warehouse->phone)->toBe('+1234567890')
        ->and($warehouse->address)->toBe('123 Warehouse St')
        ->and($warehouse->city)->toBe('New York')
        ->and($warehouse->country)->toBe('USA')
        ->and($warehouse->is_active)->toBeFalse();
});

it('creates warehouse with is_active set to false', function (): void {
    $action = resolve(CreateWarehouse::class);

    $warehouse = $action->handle([
        'name' => 'Inactive Warehouse',
        'code' => 'WH-INACTIVE',
        'is_active' => false,
    ]);

    expect($warehouse->is_active)->toBeFalse();
});

it('creates warehouse with various code formats', function (): void {
    $action = resolve(CreateWarehouse::class);

    $warehouse = $action->handle([
        'name' => 'Test Warehouse',
        'code' => 'WH-ABC-123-XYZ',
    ]);

    expect($warehouse->code)->toBe('WH-ABC-123-XYZ');
});

it('creates warehouse with null optional fields', function (): void {
    $action = resolve(CreateWarehouse::class);

    $warehouse = $action->handle([
        'name' => 'Minimal Warehouse',
        'code' => 'WH-MIN',
        'email' => null,
        'phone' => null,
        'address' => null,
        'city' => null,
        'country' => null,
    ]);

    expect($warehouse->email)->toBeNull()
        ->and($warehouse->phone)->toBeNull()
        ->and($warehouse->address)->toBeNull()
        ->and($warehouse->city)->toBeNull()
        ->and($warehouse->country)->toBeNull();
});
