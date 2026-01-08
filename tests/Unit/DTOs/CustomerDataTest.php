<?php

declare(strict_types=1);

use App\DTOs\CustomerData;
use App\Enums\CustomerTypeEnum;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Exceptions\CannotCreateData;

it('creates customer DTO from array', function (): void {
    $data = CustomerData::from([
        'name' => 'John Doe',
        'phone' => '1234567890',
        'email' => 'john@example.com',
        'address' => '123 Main St',
        'customer_type' => 'walk-in',
    ]);

    expect($data->name)->toBe('John Doe')
        ->and($data->phone)->toBe('1234567890')
        ->and($data->email)->toBe('john@example.com')
        ->and($data->address)->toBe('123 Main St')
        ->and($data->customerType)->toBeInstanceOf(CustomerTypeEnum::class)
        ->and($data->customerType)->toBe(CustomerTypeEnum::WALK_IN);
});

it('creates customer DTO with minimal data', function (): void {
    $data = CustomerData::from([
        'name' => 'Jane Doe',
        'customer_type' => 'business',
    ]);

    expect($data->name)->toBe('Jane Doe')
        ->and($data->phone)->toBeNull()
        ->and($data->email)->toBeNull()
        ->and($data->address)->toBeNull()
        ->and($data->customerType)->toBe(CustomerTypeEnum::BUSINESS);
});

it('validates required name field', function (): void {
    CustomerData::from([
        'customer_type' => 'walk-in',
    ]);
})->throws(CannotCreateData::class);

it('validates email format', function (): void {
    CustomerData::validateAndCreate([
        'name' => 'Test',
        'email' => 'invalid-email',
        'customer_type' => 'walk-in',
    ]);
})->throws(ValidationException::class);

it('validates customer type with enum', function (): void {
    $data = CustomerData::from([
        'name' => 'John Doe',
        'customer_type' => 'business',
    ]);

    expect($data->customerType)->toBeInstanceOf(CustomerTypeEnum::class)
        ->and($data->customerType)->toBe(CustomerTypeEnum::BUSINESS);
});

it('accepts enum case values for customer type', function (): void {
    $data = CustomerData::from([
        'name' => 'John Doe',
        'customer_type' => CustomerTypeEnum::REGULAR->value,
    ]);

    expect($data->customerType)->toBe(CustomerTypeEnum::REGULAR);
});

it('can use enum directly for customer type', function (): void {
    $data = CustomerData::from([
        'name' => 'John Doe',
        'customer_type' => CustomerTypeEnum::WALK_IN,
    ]);

    expect($data->customerType)->toBe(CustomerTypeEnum::WALK_IN);
});

it('rejects invalid customer type', function (): void {
    CustomerData::validateAndCreate([
        'name' => 'John Doe',
        'customer_type' => 'invalid-type',
    ]);
})->throws(ValidationException::class);

it('uses default customer type', function (): void {
    $data = CustomerData::from([
        'name' => 'Test User',
    ]);

    expect($data->customerType)->toBe(CustomerTypeEnum::WALK_IN);
});

it('handles snake_case to camelCase mapping', function (): void {
    $data = CustomerData::validateAndCreate([
        'name' => 'Test User',
        'customer_type' => 'regular',
    ]);

    expect($data->customerType)->toBe(CustomerTypeEnum::REGULAR);
});
