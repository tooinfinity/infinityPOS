<?php

declare(strict_types=1);

use App\Actions\Warehouse\UpdateWarehouse;
use App\Data\Warehouse\WarehouseData;
use App\Models\Warehouse;

it('may update a warehouse name', function (): void {
    $warehouse = Warehouse::factory()->create([
        'name' => 'Old Name',
        'code' => 'WH-001',
    ]);

    $action = resolve(UpdateWarehouse::class);

    $data = new WarehouseData(
        name: 'New Name',
        code: $warehouse->code,
        email: $warehouse->email,
        phone: $warehouse->phone,
        address: $warehouse->address,
        city: $warehouse->city,
        country: $warehouse->country,
        is_active: $warehouse->is_active,
    );

    $action->handle($warehouse, $data);

    expect($warehouse->fresh()->name)->toBe('New Name')
        ->and($warehouse->fresh()->code)->toBe('WH-001');
});

it('may update a warehouse code', function (): void {
    $warehouse = Warehouse::factory()->create([
        'name' => 'Warehouse Name',
        'code' => 'WH-OLD',
    ]);

    $action = resolve(UpdateWarehouse::class);

    $data = new WarehouseData(
        name: $warehouse->name,
        code: 'WH-NEW',
        email: $warehouse->email,
        phone: $warehouse->phone,
        address: $warehouse->address,
        city: $warehouse->city,
        country: $warehouse->country,
        is_active: $warehouse->is_active,
    );

    $action->handle($warehouse, $data);

    expect($warehouse->fresh()->code)->toBe('WH-NEW')
        ->and($warehouse->fresh()->name)->toBe('Warehouse Name');
});

it('updates multiple fields at once', function (): void {
    $warehouse = Warehouse::factory()->create([
        'name' => 'Old Name',
        'code' => 'WH-OLD',
        'email' => 'old@example.com',
        'phone' => '1234567890',
    ]);

    $action = resolve(UpdateWarehouse::class);

    $data = new WarehouseData(
        name: 'New Name',
        code: 'WH-NEW',
        email: 'new@example.com',
        phone: '9876543210',
        address: $warehouse->address,
        city: $warehouse->city,
        country: $warehouse->country,
        is_active: $warehouse->is_active,
    );

    $action->handle($warehouse, $data);

    $fresh = $warehouse->fresh();
    expect($fresh->name)->toBe('New Name')
        ->and($fresh->code)->toBe('WH-NEW')
        ->and($fresh->email)->toBe('new@example.com')
        ->and($fresh->phone)->toBe('9876543210');
});

it('updates location fields', function (): void {
    $warehouse = Warehouse::factory()->create([
        'address' => 'Old Address',
        'city' => 'Old City',
        'country' => 'Old Country',
    ]);

    $action = resolve(UpdateWarehouse::class);

    $data = new WarehouseData(
        name: $warehouse->name,
        code: $warehouse->code,
        email: $warehouse->email,
        phone: $warehouse->phone,
        address: 'New Address',
        city: 'New City',
        country: 'New Country',
        is_active: $warehouse->is_active,
    );

    $action->handle($warehouse, $data);

    $fresh = $warehouse->fresh();
    expect($fresh->address)->toBe('New Address')
        ->and($fresh->city)->toBe('New City')
        ->and($fresh->country)->toBe('New Country');
});

it('updates is_active status', function (): void {
    $warehouse = Warehouse::factory()->create([
        'is_active' => true,
    ]);

    $action = resolve(UpdateWarehouse::class);

    $data = new WarehouseData(
        name: $warehouse->name,
        code: $warehouse->code,
        email: $warehouse->email,
        phone: $warehouse->phone,
        address: $warehouse->address,
        city: $warehouse->city,
        country: $warehouse->country,
        is_active: false,
    );

    $action->handle($warehouse, $data);

    expect($warehouse->fresh()->is_active)->toBeFalse();
});

it('activates inactive warehouse', function (): void {
    $warehouse = Warehouse::factory()->create([
        'is_active' => false,
    ]);

    $action = resolve(UpdateWarehouse::class);

    $data = new WarehouseData(
        name: $warehouse->name,
        code: $warehouse->code,
        email: $warehouse->email,
        phone: $warehouse->phone,
        address: $warehouse->address,
        city: $warehouse->city,
        country: $warehouse->country,
        is_active: true,
    );

    $action->handle($warehouse, $data);

    expect($warehouse->fresh()->is_active)->toBeTrue();
});

it('sets nullable fields to null', function (): void {
    $warehouse = Warehouse::factory()->create([
        'email' => 'test@example.com',
        'phone' => '1234567890',
    ]);

    $action = resolve(UpdateWarehouse::class);

    $data = new WarehouseData(
        name: $warehouse->name,
        code: $warehouse->code,
        email: null,
        phone: null,
        address: $warehouse->address,
        city: $warehouse->city,
        country: $warehouse->country,
        is_active: $warehouse->is_active,
    );

    $action->handle($warehouse, $data);

    $fresh = $warehouse->fresh();
    expect($fresh->email)->toBeNull()
        ->and($fresh->phone)->toBeNull();
});

it('keeps unchanged fields intact', function (): void {
    $warehouse = Warehouse::factory()->create([
        'name' => 'Warehouse Name',
        'code' => 'WH-001',
        'email' => 'test@example.com',
    ]);

    $action = resolve(UpdateWarehouse::class);

    $data = new WarehouseData(
        name: $warehouse->name,
        code: $warehouse->code,
        email: $warehouse->email,
        phone: $warehouse->phone,
        address: $warehouse->address,
        city: 'New City',
        country: $warehouse->country,
        is_active: $warehouse->is_active,
    );

    $action->handle($warehouse, $data);

    $fresh = $warehouse->fresh();
    expect($fresh->name)->toBe('Warehouse Name')
        ->and($fresh->code)->toBe('WH-001')
        ->and($fresh->email)->toBe('test@example.com')
        ->and($fresh->city)->toBe('New City');
});
