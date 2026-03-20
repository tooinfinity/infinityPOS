<?php

declare(strict_types=1);

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;

it('renders suppliers index page', function (): void {
    $user = User::factory()->create();
    Supplier::factory()->count(3)->create();

    $response = $this->actingAs($user)
        ->get(route('suppliers.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('suppliers/index')
            ->has('suppliers.data')
            ->has('filters'));
});

it('renders suppliers index with search filter', function (): void {
    $user = User::factory()->create();
    Supplier::factory()->create(['name' => 'Acme Corp']);
    Supplier::factory()->create(['name' => 'Globex Inc']);

    $response = $this->actingAs($user)
        ->get(route('suppliers.index', ['search' => 'Acme']));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('suppliers/index')
            ->has('suppliers.data', 1)
            ->where('filters.search', 'Acme'));
});

it('redirects guests from suppliers index', function (): void {
    $response = $this->get(route('suppliers.index'));

    $response->assertRedirectToRoute('login');
});

it('may create a supplier', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('suppliers.store'), [
            'name' => 'Test Supplier',
            'company_name' => 'Test Company',
            'email' => 'supplier@example.com',
            'phone' => '+1234567890',
            'address' => '123 Business St',
            'city' => 'New York',
            'country' => 'USA',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('suppliers.show', Supplier::query()->first());

    $supplier = Supplier::query()->where('name', 'Test Supplier')->first();
    expect($supplier)->not->toBeNull()
        ->and($supplier->name)->toBe('Test Supplier')
        ->and($supplier->company_name)->toBe('Test Company')
        ->and($supplier->email)->toBe('supplier@example.com')
        ->and($supplier->is_active)->toBeTrue();
});

it('requires name to create supplier', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('suppliers.store'), [
            'email' => 'supplier@example.com',
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('name');

    expect(Supplier::query()->count())->toBe(0);
});

it('requires min 3 characters for supplier name', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('suppliers.store'), [
            'name' => 'AB',
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('name');
});

it('requires max 80 characters for supplier name', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('suppliers.store'), [
            'name' => str_repeat('A', 81),
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('name');
});

it('requires unique supplier name', function (): void {
    $user = User::factory()->create();
    Supplier::factory()->create(['name' => 'Acme Corp']);

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('suppliers.store'), [
            'name' => 'Acme Corp',
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('name');

    expect(Supplier::query()->count())->toBe(1);
});

it('requires valid email format for supplier', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('suppliers.store'), [
            'name' => 'Test Supplier',
            'email' => 'invalid-email',
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('email');
});

it('allows creating supplier without optional fields', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('suppliers.store'), [
            'name' => 'Test Supplier',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('suppliers.show', Supplier::query()->first())
        ->assertSessionDoesntHaveErrors();

    $supplier = Supplier::query()->where('name', 'Test Supplier')->first();
    expect($supplier)->not->toBeNull()
        ->and($supplier->email)->toBeNull()
        ->and($supplier->phone)->toBeNull()
        ->and($supplier->address)->toBeNull();
});

it('redirects guests from store supplier', function (): void {
    $response = $this->post(route('suppliers.store'), [
        'name' => 'Test Supplier',
        'is_active' => true,
    ]);

    $response->assertRedirectToRoute('login');
});

it('renders show supplier page', function (): void {
    $user = User::factory()->create();
    $supplier = Supplier::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('suppliers.show', $supplier));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('suppliers/show')
            ->has('supplier'));
});

it('includes purchase count and recent purchases on show page', function (): void {
    $user = User::factory()->create();
    $supplier = Supplier::factory()->create();
    Purchase::factory()->count(3)->create(['supplier_id' => $supplier->id]);

    $response = $this->actingAs($user)
        ->get(route('suppliers.show', $supplier));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('suppliers/show')
            ->where('supplier.purchases_count', 3)
            ->has('supplier.purchases', 3));
});

it('redirects guests from show supplier page', function (): void {
    $supplier = Supplier::factory()->create();

    $response = $this->get(route('suppliers.show', $supplier));

    $response->assertRedirectToRoute('login');
});

it('may update a supplier', function (): void {
    $user = User::factory()->create();
    $supplier = Supplier::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($user)
        ->fromRoute('suppliers.show', $supplier)
        ->put(route('suppliers.update', $supplier), [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'phone' => '+9876543210',
            'is_active' => false,
        ]);

    $response->assertRedirectToRoute('suppliers.show', $supplier);

    expect($supplier->refresh()->name)->toBe('New Name')
        ->and($supplier->email)->toBe('new@example.com')
        ->and($supplier->phone)->toBe('+9876543210')
        ->and($supplier->is_active)->toBeFalse();
});

it('requires name on supplier update', function (): void {
    $user = User::factory()->create();
    $supplier = Supplier::factory()->create(['name' => 'Original Name']);

    $response = $this->actingAs($user)
        ->fromRoute('suppliers.show', $supplier)
        ->put(route('suppliers.update', $supplier), [
            'email' => 'new@example.com',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('suppliers.show', $supplier)
        ->assertSessionHasErrors('name');

    expect($supplier->refresh()->name)->toBe('Original Name');
});

it('allows updating supplier to same name', function (): void {
    $user = User::factory()->create();
    $supplier = Supplier::factory()->create(['name' => 'Same Name']);

    $response = $this->actingAs($user)
        ->fromRoute('suppliers.show', $supplier)
        ->put(route('suppliers.update', $supplier), [
            'name' => 'Same Name',
            'is_active' => false,
        ]);

    $response->assertRedirectToRoute('suppliers.show', $supplier)
        ->assertSessionDoesntHaveErrors();

    expect($supplier->refresh()->is_active)->toBeFalse();
});

it('prevents updating to existing supplier name', function (): void {
    $user = User::factory()->create();
    Supplier::factory()->create(['name' => 'Existing Name']);
    $supplier = Supplier::factory()->create(['name' => 'Original Name']);

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->put(route('suppliers.update', $supplier), [
            'name' => 'Existing Name',
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('name');
});

it('redirects guests from update supplier', function (): void {
    $supplier = Supplier::factory()->create();

    $response = $this->put(route('suppliers.update', $supplier), [
        'name' => 'New Name',
        'is_active' => true,
    ]);

    $response->assertRedirectToRoute('login');
});

it('may delete a supplier', function (): void {
    $user = User::factory()->create();
    $supplier = Supplier::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('suppliers.index')
        ->delete(route('suppliers.destroy', $supplier));

    $response->assertRedirectToRoute('suppliers.index');

    expect($supplier->fresh())->toBeNull();
});

it('prevents deleting supplier with associated purchases', function (): void {
    $user = User::factory()->create();
    $supplier = Supplier::factory()->create();
    Purchase::factory()->create(['supplier_id' => $supplier->id]);

    $response = $this->actingAs($user)
        ->fromRoute('suppliers.index')
        ->delete(route('suppliers.destroy', $supplier));

    $response->assertRedirectToRoute('suppliers.index')
        ->assertSessionHas('error');

    expect($supplier->fresh())->not->toBeNull();
});

it('redirects guests from delete supplier', function (): void {
    $supplier = Supplier::factory()->create();

    $response = $this->delete(route('suppliers.destroy', $supplier));

    $response->assertRedirectToRoute('login');
});
