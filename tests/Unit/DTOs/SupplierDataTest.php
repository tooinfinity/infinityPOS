<?php

declare(strict_types=1);

use App\DTOs\SupplierData;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Exceptions\CannotCreateData;

it('creates supplier DTO from array', function (): void {
    $data = SupplierData::from([
        'name' => 'ABC Supplies',
        'contact_person' => 'John Smith',
        'phone' => '9876543210',
        'email' => 'contact@abc.com',
        'address' => '456 Business Ave',
    ]);

    expect($data->name)->toBe('ABC Supplies')
        ->and($data->contactPerson)->toBe('John Smith')
        ->and($data->phone)->toBe('9876543210')
        ->and($data->email)->toBe('contact@abc.com')
        ->and($data->address)->toBe('456 Business Ave');
});

it('creates supplier DTO with only name', function (): void {
    $data = SupplierData::from([
        'name' => 'XYZ Corp',
    ]);

    expect($data->name)->toBe('XYZ Corp')
        ->and($data->contactPerson)->toBeNull()
        ->and($data->phone)->toBeNull()
        ->and($data->email)->toBeNull()
        ->and($data->address)->toBeNull();
});

it('validates required name field', function (): void {
    SupplierData::from([
        'phone' => '1234567890',
    ]);
})->throws(CannotCreateData::class);

it('validates email format', function (): void {
    SupplierData::validateAndCreate([
        'name' => 'Test Supplier',
        'email' => 'not-an-email',
    ]);
})->throws(ValidationException::class);

it('handles snake_case mapping', function (): void {
    $data = SupplierData::from([
        'name' => 'Test',
        'contact_person' => 'Jane Doe',
    ]);

    expect($data->contactPerson)->toBe('Jane Doe');
});
