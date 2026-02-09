<?php

declare(strict_types=1);

use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

it('has many sales', function (): void {
    $customer = new Customer();

    expect($customer->sales())
        ->toBeInstanceOf(HasMany::class);
});

it('can create sales', function (): void {
    $customer = Customer::factory()->create();
    Sale::factory()->count(3)->create(['customer_id' => $customer->id]);

    expect($customer->sales)
        ->toHaveCount(3)
        ->each->toBeInstanceOf(Sale::class);
});

it('returns empty collection when no sales exist', function (): void {
    $customer = Customer::factory()->create();

    expect($customer->sales)
        ->toBeEmpty()
        ->toBeInstanceOf(Collection::class);
});
