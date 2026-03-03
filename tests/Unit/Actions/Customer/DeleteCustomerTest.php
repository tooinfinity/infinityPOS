<?php

declare(strict_types=1);

use App\Actions\Customer\DeleteCustomer;
use App\Exceptions\InvalidOperationException;
use App\Models\Customer;
use App\Models\Sale;

it('may delete a customer', function (): void {
    $customer = Customer::factory()->create();

    $action = resolve(DeleteCustomer::class);

    $result = $action->handle($customer);

    expect($result)->toBeTrue()
        ->and($customer->exists)->toBeFalse();
});

it('throws exception when deleting customer with sales', function (): void {
    $customer = Customer::factory()->create();
    Sale::factory()->create([
        'customer_id' => $customer->id,
    ]);

    $action = resolve(DeleteCustomer::class);

    expect(fn () => $action->handle($customer))
        ->toThrow(InvalidOperationException::class, 'Cannot delete Customer. Cannot delete customer with associated sales.');
});

it('throws exception when deleting customer with multiple sales', function (): void {
    $customer = Customer::factory()->create();
    Sale::factory()->count(3)->create([
        'customer_id' => $customer->id,
    ]);

    $action = resolve(DeleteCustomer::class);

    expect(fn () => $action->handle($customer))
        ->toThrow(InvalidOperationException::class, 'Cannot delete Customer. Cannot delete customer with associated sales.');
});

it('deletes customer without sales', function (): void {
    $customer = Customer::factory()->create();

    $action = resolve(DeleteCustomer::class);

    $result = $action->handle($customer);

    expect($result)->toBeTrue()
        ->and(Customer::query()->find($customer->id))->toBeNull();
});

it('removes customer from database', function (): void {
    $customer = Customer::factory()->create();

    $action = resolve(DeleteCustomer::class);
    $action->handle($customer);

    $this->assertDatabaseMissing('customers', [
        'id' => $customer->id,
    ]);
});

it('does not delete customer when sales exist', function (): void {
    $customer = Customer::factory()->create();
    Sale::factory()->create([
        'customer_id' => $customer->id,
    ]);

    $action = resolve(DeleteCustomer::class);

    try {
        $action->handle($customer);
    } catch (InvalidOperationException) {
        // Expected exception
    }

    expect(Customer::query()->find($customer->id))->not->toBeNull();
});
