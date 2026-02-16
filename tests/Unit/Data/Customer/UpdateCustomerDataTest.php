<?php

declare(strict_types=1);

use App\Data\Customer\UpdateCustomerData;
use Spatie\LaravelData\Optional;

it('may be created with all fields', function (): void {
    $data = new UpdateCustomerData(
        name: 'Updated Customer',
        email: 'updated@example.com',
        phone: '0987654321',
        address: '456 Updated St',
        city: 'Updated City',
        country: 'Updated Country',
        is_active: false,
    );

    expect($data)
        ->name->toBe('Updated Customer')
        ->email->toBe('updated@example.com')
        ->phone->toBe('0987654321')
        ->address->toBe('456 Updated St')
        ->city->toBe('Updated City')
        ->country->toBe('Updated Country')
        ->is_active->toBeFalse();
});

it('may be created with Optional fields', function (): void {
    $data = new UpdateCustomerData(
        name: Optional::create(),
        email: Optional::create(),
        phone: Optional::create(),
        address: Optional::create(),
        city: Optional::create(),
        country: Optional::create(),
        is_active: Optional::create(),
    );

    expect($data->name)->toBeInstanceOf(Optional::class)
        ->and($data->email)->toBeInstanceOf(Optional::class)
        ->and($data->phone)->toBeInstanceOf(Optional::class)
        ->and($data->address)->toBeInstanceOf(Optional::class)
        ->and($data->city)->toBeInstanceOf(Optional::class)
        ->and($data->country)->toBeInstanceOf(Optional::class)
        ->and($data->is_active)->toBeInstanceOf(Optional::class);
});

it('may be created with mixed Optional and value fields', function (): void {
    $data = new UpdateCustomerData(
        name: 'New Name',
        email: Optional::create(),
        phone: '1234567890',
        address: Optional::create(),
        city: 'New City',
        country: Optional::create(),
        is_active: true,
    );

    expect($data)
        ->name->toBe('New Name')
        ->email->toBeInstanceOf(Optional::class)
        ->phone->toBe('1234567890')
        ->address->toBeInstanceOf(Optional::class)
        ->city->toBe('New City')
        ->country->toBeInstanceOf(Optional::class)
        ->is_active->toBeTrue();
});

it('may be created with null values', function (): void {
    $data = new UpdateCustomerData(
        name: Optional::create(),
        email: null,
        phone: null,
        address: null,
        city: null,
        country: null,
        is_active: Optional::create(),
    );

    expect($data->email)->toBeNull()
        ->and($data->phone)->toBeNull()
        ->and($data->address)->toBeNull()
        ->and($data->city)->toBeNull()
        ->and($data->country)->toBeNull();
});
