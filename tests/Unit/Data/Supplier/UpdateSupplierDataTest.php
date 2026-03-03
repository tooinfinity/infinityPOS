<?php

declare(strict_types=1);

use App\Data\Supplier\UpdateSupplierData;
use Spatie\LaravelData\Optional;

it('may be created with all fields', function (): void {
    $data = new UpdateSupplierData(
        name: 'Updated Supplier',
        company_name: 'Updated Company',
        email: 'updated@example.com',
        phone: '0987654321',
        address: '456 Updated St',
        city: 'Updated City',
        country: 'Updated Country',
        is_active: false,
    );

    expect($data)
        ->name->toBe('Updated Supplier')
        ->company_name->toBe('Updated Company')
        ->email->toBe('updated@example.com')
        ->phone->toBe('0987654321')
        ->address->toBe('456 Updated St')
        ->city->toBe('Updated City')
        ->country->toBe('Updated Country')
        ->is_active->toBeFalse();
});

it('may be created with Optional fields', function (): void {
    $data = new UpdateSupplierData(
        name: Optional::create(),
        company_name: Optional::create(),
        email: Optional::create(),
        phone: Optional::create(),
        address: Optional::create(),
        city: Optional::create(),
        country: Optional::create(),
        is_active: Optional::create(),
    );

    expect($data->name)->toBeInstanceOf(Optional::class)
        ->and($data->company_name)->toBeInstanceOf(Optional::class)
        ->and($data->email)->toBeInstanceOf(Optional::class)
        ->and($data->phone)->toBeInstanceOf(Optional::class)
        ->and($data->address)->toBeInstanceOf(Optional::class)
        ->and($data->city)->toBeInstanceOf(Optional::class)
        ->and($data->country)->toBeInstanceOf(Optional::class)
        ->and($data->is_active)->toBeInstanceOf(Optional::class);
});

it('may be created with mixed Optional and value fields', function (): void {
    $data = new UpdateSupplierData(
        name: 'New Name',
        company_name: Optional::create(),
        email: 'new@example.com',
        phone: Optional::create(),
        address: Optional::create(),
        city: 'New City',
        country: Optional::create(),
        is_active: true,
    );

    expect($data)
        ->name->toBe('New Name')
        ->company_name->toBeInstanceOf(Optional::class)
        ->email->toBe('new@example.com')
        ->phone->toBeInstanceOf(Optional::class)
        ->address->toBeInstanceOf(Optional::class)
        ->city->toBe('New City')
        ->country->toBeInstanceOf(Optional::class)
        ->is_active->toBeTrue();
});

it('may be created with null values', function (): void {
    $data = new UpdateSupplierData(
        name: Optional::create(),
        company_name: null,
        email: null,
        phone: null,
        address: null,
        city: null,
        country: null,
        is_active: Optional::create(),
    );

    expect($data->company_name)->toBeNull()
        ->and($data->email)->toBeNull()
        ->and($data->phone)->toBeNull()
        ->and($data->address)->toBeNull()
        ->and($data->city)->toBeNull()
        ->and($data->country)->toBeNull();
});
