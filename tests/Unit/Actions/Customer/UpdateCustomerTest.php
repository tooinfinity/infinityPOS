<?php

declare(strict_types=1);

use App\Actions\Customer\UpdateCustomer;
use App\Data\Customer\CustomerData;
use App\Models\Customer;

it('may update a customer name', function (): void {
    $customer = Customer::factory()->create([
        'name' => 'Old Name',
    ]);

    $action = resolve(UpdateCustomer::class);

    $data = new CustomerData(
        name: 'New Name',
        email: $customer->email,
        phone: $customer->phone,
        address: $customer->address,
        city: $customer->city,
        country: $customer->country,
        is_active: $customer->is_active,
    );

    $updatedCustomer = $action->handle($customer, $data);

    expect($updatedCustomer->name)->toBe('New Name');
});

it('may update all customer fields', function (): void {
    $customer = Customer::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
        'phone' => '1111111111',
        'address' => 'Old Address',
        'city' => 'Old City',
        'country' => 'Old Country',
        'is_active' => true,
    ]);

    $action = resolve(UpdateCustomer::class);

    $data = new CustomerData(
        name: 'New Name',
        email: 'new@example.com',
        phone: '2222222222',
        address: 'New Address',
        city: 'New City',
        country: 'New Country',
        is_active: false,
    );

    $updatedCustomer = $action->handle($customer, $data);

    expect($updatedCustomer->name)->toBe('New Name')
        ->and($updatedCustomer->email)->toBe('new@example.com')
        ->and($updatedCustomer->phone)->toBe('2222222222')
        ->and($updatedCustomer->address)->toBe('New Address')
        ->and($updatedCustomer->city)->toBe('New City')
        ->and($updatedCustomer->country)->toBe('New Country')
        ->and($updatedCustomer->is_active)->toBeFalse();
});

it('partially updates customer with Optional fields', function (): void {
    $customer = Customer::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
        'phone' => '3333333333',
        'is_active' => true,
    ]);

    $action = resolve(UpdateCustomer::class);

    $data = new CustomerData(
        name: $customer->name,
        email: 'updated@example.com',
        phone: $customer->phone,
        address: $customer->address,
        city: $customer->city,
        country: $customer->country,
        is_active: false,
    );

    $updatedCustomer = $action->handle($customer, $data);

    expect($updatedCustomer->name)->toBe('Original Name')
        ->and($updatedCustomer->email)->toBe('updated@example.com')
        ->and($updatedCustomer->phone)->toBe('3333333333')
        ->and($updatedCustomer->is_active)->toBeFalse();
});

it('persists updates to database', function (): void {
    $customer = Customer::factory()->create([
        'name' => 'Original Name',
    ]);

    $action = resolve(UpdateCustomer::class);

    $data = new CustomerData(
        name: 'Persisted Name',
        email: $customer->email,
        phone: $customer->phone,
        address: $customer->address,
        city: $customer->city,
        country: $customer->country,
        is_active: $customer->is_active,
    );

    $action->handle($customer, $data);

    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'name' => 'Persisted Name',
    ]);
});
