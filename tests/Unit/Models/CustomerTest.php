<?php

declare(strict_types=1);

use App\Models\Customer;

test('to array', function (): void {
    $customer = Customer::factory()->create()->refresh();

    expect(array_keys($customer->toArray()))
        ->toBe([
            'id',
            'name',
            'email',
            'phone',
            'address',
            'city',
            'country',
            'is_active',
            'created_at',
            'updated_at',
        ]);
});

test('only returns active customers by default', function (): void {
    Customer::factory()->count(2)->create([
        'is_active' => true,
    ]);
    Customer::factory()->count(2)->create([
        'is_active' => false,
    ]);

    $customers = Customer::all();

    expect($customers)
        ->toHaveCount(2);
});
