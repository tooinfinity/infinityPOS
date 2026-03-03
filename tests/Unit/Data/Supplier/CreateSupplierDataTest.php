<?php

declare(strict_types=1);

use App\Data\Supplier\CreateSupplierData;

it('may be created with required fields', function (): void {
    $data = new CreateSupplierData(
        name: 'Test Supplier',
        company_name: 'Test Company',
        email: 'test@example.com',
        phone: '1234567890',
        address: '123 Test St',
        city: 'Test City',
        country: 'Test Country',
        is_active: true,
    );

    expect($data)
        ->name->toBe('Test Supplier')
        ->company_name->toBe('Test Company')
        ->email->toBe('test@example.com')
        ->phone->toBe('1234567890')
        ->address->toBe('123 Test St')
        ->city->toBe('Test City')
        ->country->toBe('Test Country')
        ->is_active->toBeTrue();
});

it('may be created with null optional fields', function (): void {
    $data = new CreateSupplierData(
        name: 'Test Supplier',
        company_name: null,
        email: null,
        phone: null,
        address: null,
        city: null,
        country: null,
        is_active: false,
    );

    expect($data)
        ->name->toBe('Test Supplier')
        ->company_name->toBeNull()
        ->email->toBeNull()
        ->phone->toBeNull()
        ->address->toBeNull()
        ->city->toBeNull()
        ->country->toBeNull()
        ->is_active->toBeFalse();
});

it('may be created with partial optional fields', function (): void {
    $data = new CreateSupplierData(
        name: 'Test Supplier',
        company_name: 'Test Company',
        email: null,
        phone: '1234567890',
        address: null,
        city: 'Test City',
        country: null,
        is_active: true,
    );

    expect($data)
        ->name->toBe('Test Supplier')
        ->company_name->toBe('Test Company')
        ->email->toBeNull()
        ->phone->toBe('1234567890')
        ->address->toBeNull()
        ->city->toBe('Test City')
        ->country->toBeNull()
        ->is_active->toBeTrue();
});
