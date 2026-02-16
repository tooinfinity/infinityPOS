<?php

declare(strict_types=1);

use App\Actions\Customer\CreateCustomer;
use App\Data\Customer\CreateCustomerData;
use App\Models\Customer;

it('may create a customer', function (): void {
    $action = resolve(CreateCustomer::class);

    $data = new CreateCustomerData(
        name: 'Test Customer',
        email: 'test@example.com',
        phone: '1234567890',
        address: '123 Test St',
        city: 'Test City',
        country: 'Test Country',
        is_active: true,
    );

    $customer = $action->handle($data);

    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->name)->toBe('Test Customer')
        ->and($customer->email)->toBe('test@example.com')
        ->and($customer->phone)->toBe('1234567890')
        ->and($customer->address)->toBe('123 Test St')
        ->and($customer->city)->toBe('Test City')
        ->and($customer->country)->toBe('Test Country')
        ->and($customer->is_active)->toBeTrue()
        ->and($customer->exists)->toBeTrue();
});

it('creates customer with minimal fields', function (): void {
    $action = resolve(CreateCustomer::class);

    $data = new CreateCustomerData(
        name: 'Minimal Customer',
        email: null,
        phone: null,
        address: null,
        city: null,
        country: null,
        is_active: true,
    );

    $customer = $action->handle($data);

    expect($customer->name)->toBe('Minimal Customer')
        ->and($customer->email)->toBeNull()
        ->and($customer->phone)->toBeNull()
        ->and($customer->address)->toBeNull()
        ->and($customer->city)->toBeNull()
        ->and($customer->country)->toBeNull()
        ->and($customer->is_active)->toBeTrue();
});

it('creates customer with is_active false', function (): void {
    $action = resolve(CreateCustomer::class);

    $data = new CreateCustomerData(
        name: 'Inactive Customer',
        email: null,
        phone: null,
        address: null,
        city: null,
        country: null,
        is_active: false,
    );

    $customer = $action->handle($data);

    expect($customer->is_active)->toBeFalse();
});

it('stores customer in database', function (): void {
    $action = resolve(CreateCustomer::class);

    $data = new CreateCustomerData(
        name: 'Database Customer',
        email: 'db@example.com',
        phone: '5555555555',
        address: '789 DB St',
        city: 'DB City',
        country: 'DB Country',
        is_active: true,
    );

    $customer = $action->handle($data);

    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'name' => 'Database Customer',
        'email' => 'db@example.com',
        'phone' => '5555555555',
        'address' => '789 DB St',
        'city' => 'DB City',
        'country' => 'DB Country',
        'is_active' => true,
    ]);
});
