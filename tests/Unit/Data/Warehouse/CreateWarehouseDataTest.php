<?php

declare(strict_types=1);

use App\Data\Warehouse\CreateWarehouseData;

it('may be created with required fields', function (): void {
    $data = new CreateWarehouseData(
        name: 'Main Warehouse',
        code: 'WH-001',
        email: null,
        phone: null,
        address: null,
        city: null,
        country: null,
        is_active: true,
    );

    expect($data)
        ->name->toBe('Main Warehouse')
        ->code->toBe('WH-001')
        ->is_active->toBeTrue();
});

it('may be created with all fields', function (): void {
    $data = new CreateWarehouseData(
        name: 'Main Warehouse',
        code: 'WH-001',
        email: 'warehouse@example.com',
        phone: '+1234567890',
        address: '123 Main St',
        city: 'New York',
        country: 'USA',
        is_active: true,
    );

    expect($data)
        ->email->toBe('warehouse@example.com')
        ->phone->toBe('+1234567890')
        ->address->toBe('123 Main St')
        ->city->toBe('New York')
        ->country->toBe('USA');
});
