<?php

declare(strict_types=1);

use App\Models\Customer;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('to array', function (): void {
    $customer = Customer::factory()->create()->refresh();

    expect(array_keys($customer->toArray()))
        ->toBe([
            'id',
            'name',
            'phone',
            'email',
            'address',
            'customer_type',
            'created_at',
            'updated_at',
        ]);
});

test('sales relationship returns has many', function (): void {
    $customer = new Customer();

    expect($customer->sales())
        ->toBeInstanceOf(HasMany::class);
});

test('invoices relationship returns has many', function (): void {
    $customer = new Customer();

    expect($customer->invoices())
        ->toBeInstanceOf(HasMany::class);
});

test('returns relationship returns has many', function (): void {
    $customer = new Customer();

    expect($customer->returns())
        ->toBeInstanceOf(HasMany::class);
});

test('casts returns correct array', function (): void {
    $customer = new Customer();

    expect($customer->casts())
        ->toBe([
            'id' => 'integer',
            'name' => 'string',
            'phone' => 'string',
            'email' => 'string',
            'address' => 'string',
            'customer_type' => App\Enums\CustomerTypeEnum::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $customer = Customer::factory()->create()->refresh();

    expect($customer->id)->toBeInt()
        ->and($customer->name)->toBeString()
        ->and($customer->created_at)->toBeInstanceOf(DateTimeInterface::class)
        ->and($customer->updated_at)->toBeInstanceOf(DateTimeInterface::class);
});

test('casts customer_type to CustomerTypeEnum', function (): void {
    $customer = Customer::factory()->create([
        'customer_type' => App\Enums\CustomerTypeEnum::REGULAR,
    ]);

    expect($customer->customer_type)->toBeInstanceOf(App\Enums\CustomerTypeEnum::class)
        ->and($customer->customer_type)->toBe(App\Enums\CustomerTypeEnum::REGULAR);
});

test('can set customer_type using enum value', function (): void {
    $customer = Customer::factory()->create([
        'customer_type' => 'business',
    ]);

    expect($customer->customer_type)->toBeInstanceOf(App\Enums\CustomerTypeEnum::class)
        ->and($customer->customer_type->value)->toBe('business');
});

test('can access enum methods on customer_type', function (): void {
    $customer = Customer::factory()->create([
        'customer_type' => App\Enums\CustomerTypeEnum::BUSINESS,
    ]);

    expect($customer->customer_type->label())->toBe('Business')
        ->and($customer->customer_type->color())->toBeString()
        ->and($customer->customer_type->icon())->toBeString()
        ->and($customer->customer_type->canCreateInvoices())->toBeTrue();
});
