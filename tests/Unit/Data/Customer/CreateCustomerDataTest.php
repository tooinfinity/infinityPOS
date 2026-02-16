<?php

declare(strict_types=1);

use App\Data\Customer\CreateCustomerData;

it('may be created with required fields', function (): void {
    $data = new CreateCustomerData(
        name: 'Test Customer',
        email: 'test@example.com',
        phone: '1234567890',
        address: '123 Test St',
        city: 'Test City',
        country: 'Test Country',
        is_active: true,
    );

    expect($data)
        ->name->toBe('Test Customer')
        ->email->toBe('test@example.com')
        ->phone->toBe('1234567890')
        ->address->toBe('123 Test St')
        ->city->toBe('Test City')
        ->country->toBe('Test Country')
        ->is_active->toBeTrue();
});

it('may be created with null optional fields', function (): void {
    $data = new CreateCustomerData(
        name: 'Test Customer',
        email: null,
        phone: null,
        address: null,
        city: null,
        country: null,
        is_active: false,
    );

    expect($data)
        ->name->toBe('Test Customer')
        ->email->toBeNull()
        ->phone->toBeNull()
        ->address->toBeNull()
        ->city->toBeNull()
        ->country->toBeNull()
        ->is_active->toBeFalse();
});

it('may be created with partial optional fields', function (): void {
    $data = new CreateCustomerData(
        name: 'Test Customer',
        email: 'test@example.com',
        phone: null,
        address: '123 Test St',
        city: null,
        country: 'Test Country',
        is_active: true,
    );

    expect($data)
        ->name->toBe('Test Customer')
        ->email->toBe('test@example.com')
        ->phone->toBeNull()
        ->address->toBe('123 Test St')
        ->city->toBeNull()
        ->country->toBe('Test Country')
        ->is_active->toBeTrue();
});
