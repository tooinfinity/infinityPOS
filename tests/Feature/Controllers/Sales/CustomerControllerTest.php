<?php

declare(strict_types=1);

use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;

it('renders customers index page', function (): void {
    $user = User::factory()->create();
    Customer::factory()->count(3)->create();

    $response = $this->actingAs($user)
        ->get(route('customers.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('customers/index')
            ->has('customers.data')
            ->has('filters'));
});

it('renders customers index with search filter', function (): void {
    $user = User::factory()->create();
    Customer::factory()->create(['name' => 'John Doe']);
    Customer::factory()->create(['name' => 'Jane Smith']);

    $response = $this->actingAs($user)
        ->get(route('customers.index', ['search' => 'John']));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('customers/index')
            ->has('customers.data', 1)
            ->where('filters.search', 'John'));
});

it('redirects guests from customers index', function (): void {
    $response = $this->get(route('customers.index'));

    $response->assertRedirectToRoute('login');
});

it('may create a customer', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('customers.store'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'address' => '123 Main St',
            'city' => 'New York',
            'country' => 'USA',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('customers.show', Customer::query()->first());

    $customer = Customer::query()->where('name', 'John Doe')->first();
    expect($customer)->not->toBeNull()
        ->and($customer->name)->toBe('John Doe')
        ->and($customer->email)->toBe('john@example.com')
        ->and($customer->phone)->toBe('+1234567890')
        ->and($customer->is_active)->toBeTrue();
});

it('requires name to create customer', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('customers.store'), [
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('name');

    expect(Customer::query()->count())->toBe(0);
});

it('requires min 3 characters for customer name', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('customers.store'), [
            'name' => 'AB',
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('name');
});

it('requires max 80 characters for customer name', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('customers.store'), [
            'name' => str_repeat('A', 81),
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('name');
});

it('requires unique customer name', function (): void {
    $user = User::factory()->create();
    Customer::factory()->create(['name' => 'John Doe']);

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('customers.store'), [
            'name' => 'John Doe',
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('name');

    expect(Customer::query()->count())->toBe(1);
});

it('requires valid email format', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('customers.store'), [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('email');
});

it('allows creating customer without optional fields', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('customers.store'), [
            'name' => 'John Doe',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('customers.show', Customer::query()->first())
        ->assertSessionDoesntHaveErrors();

    $customer = Customer::query()->where('name', 'John Doe')->first();
    expect($customer)->not->toBeNull()
        ->and($customer->email)->toBeNull()
        ->and($customer->phone)->toBeNull()
        ->and($customer->address)->toBeNull();
});

it('redirects guests from store customer', function (): void {
    $response = $this->post(route('customers.store'), [
        'name' => 'John Doe',
        'is_active' => true,
    ]);

    $response->assertRedirectToRoute('login');
});

it('renders show customer page', function (): void {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('customers.show', $customer));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('customers/show')
            ->has('customer'));
});

it('includes sales count and recent sales on show page', function (): void {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    Sale::factory()->count(3)->create(['customer_id' => $customer->id]);

    $response = $this->actingAs($user)
        ->get(route('customers.show', $customer));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('customers/show')
            ->where('customer.sales_count', 3)
            ->has('customer.sales', 3));
});

it('redirects guests from show customer page', function (): void {
    $customer = Customer::factory()->create();

    $response = $this->get(route('customers.show', $customer));

    $response->assertRedirectToRoute('login');
});

it('may update a customer', function (): void {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($user)
        ->fromRoute('customers.show', $customer)
        ->put(route('customers.update', $customer), [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'phone' => '+9876543210',
            'is_active' => false,
        ]);

    $response->assertRedirectToRoute('customers.show', $customer);

    expect($customer->refresh()->name)->toBe('New Name')
        ->and($customer->email)->toBe('new@example.com')
        ->and($customer->phone)->toBe('+9876543210')
        ->and($customer->is_active)->toBeFalse();
});

it('requires name on customer update', function (): void {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['name' => 'Original Name']);

    $response = $this->actingAs($user)
        ->fromRoute('customers.show', $customer)
        ->put(route('customers.update', $customer), [
            'email' => 'new@example.com',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('customers.show', $customer)
        ->assertSessionHasErrors('name');

    expect($customer->refresh()->name)->toBe('Original Name');
});

it('allows updating customer to same name', function (): void {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['name' => 'Same Name']);

    $response = $this->actingAs($user)
        ->fromRoute('customers.show', $customer)
        ->put(route('customers.update', $customer), [
            'name' => 'Same Name',
            'is_active' => false,
        ]);

    $response->assertRedirectToRoute('customers.show', $customer)
        ->assertSessionDoesntHaveErrors();

    expect($customer->refresh()->is_active)->toBeFalse();
});

it('prevents updating to existing customer name', function (): void {
    $user = User::factory()->create();
    Customer::factory()->create(['name' => 'Existing Name']);
    $customer = Customer::factory()->create(['name' => 'Original Name']);

    $response = $this->actingAs($user)
        ->fromRoute('customers.show', $customer)
        ->put(route('customers.update', $customer), [
            'name' => 'Existing Name',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('customers.show', $customer)
        ->assertSessionHasErrors('name');
});

it('redirects guests from update customer', function (): void {
    $customer = Customer::factory()->create();

    $response = $this->put(route('customers.update', $customer), [
        'name' => 'New Name',
        'is_active' => true,
    ]);

    $response->assertRedirectToRoute('login');
});

it('may delete a customer', function (): void {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('customers.index')
        ->delete(route('customers.destroy', $customer));

    $response->assertRedirectToRoute('customers.index');

    expect($customer->fresh())->toBeNull();
});

it('prevents deleting customer with associated sales', function (): void {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    Sale::factory()->create(['customer_id' => $customer->id]);

    $response = $this->actingAs($user)
        ->fromRoute('customers.index')
        ->delete(route('customers.destroy', $customer));

    $response->assertRedirectToRoute('customers.index')
        ->assertSessionHas('error');

    expect($customer->fresh())->not->toBeNull();
});

it('redirects guests from delete customer', function (): void {
    $customer = Customer::factory()->create();

    $response = $this->delete(route('customers.destroy', $customer));

    $response->assertRedirectToRoute('login');
});
