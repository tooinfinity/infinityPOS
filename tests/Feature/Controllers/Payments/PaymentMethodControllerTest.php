<?php

declare(strict_types=1);

use App\Models\PaymentMethod;
use App\Models\User;

it('renders payment methods index page', function (): void {
    $user = User::factory()->create();
    PaymentMethod::factory()->count(3)->create();

    $response = $this->actingAs($user)
        ->get(route('payment-methods.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('payment-method/index')
            ->has('methods.data')
            ->has('filters'));
});

it('renders payment methods index with search filter', function (): void {
    $user = User::factory()->create();
    PaymentMethod::factory()->create(['name' => 'Cash']);
    PaymentMethod::factory()->create(['name' => 'Card']);

    $response = $this->actingAs($user)
        ->get(route('payment-methods.index', ['search' => 'Cash']));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('payment-method/index')
            ->has('methods.data', 1)
            ->where('filters.search', 'Cash'));
});

it('redirects guests from payment methods index', function (): void {
    $response = $this->get(route('payment-methods.index'));

    $response->assertRedirectToRoute('login');
});

it('renders create payment method page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('payment-methods.create'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page->component('payment-method/create'));
});

it('redirects guests from create payment method page', function (): void {
    $response = $this->get(route('payment-methods.create'));

    $response->assertRedirectToRoute('login');
});

it('may create a payment method', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('payment-methods.store'), [
            'name' => 'Cash',
            'code' => 'cash',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('payment-methods.index');

    $method = PaymentMethod::query()->where('name', 'Cash')->first();
    expect($method)->not->toBeNull()
        ->and($method->name)->toBe('Cash')
        ->and($method->code)->toBe('cash')
        ->and($method->is_active)->toBeTrue();
});

it('requires name to create payment method', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('payment-methods.store'), [
            'code' => 'cash',
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('name');

    expect(PaymentMethod::query()->count())->toBe(0);
});

it('requires code to create payment method', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('payment-methods.store'), [
            'name' => 'Cash',
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('code');

    expect(PaymentMethod::query()->count())->toBe(0);
});

it('requires unique payment method name', function (): void {
    $user = User::factory()->create();
    PaymentMethod::factory()->create(['name' => 'Cash', 'code' => 'cash']);

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('payment-methods.store'), [
            'name' => 'Cash',
            'code' => 'new_code',
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('name');
});

it('requires unique payment method code', function (): void {
    $user = User::factory()->create();
    PaymentMethod::factory()->create(['name' => 'Cash', 'code' => 'cash']);

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('payment-methods.store'), [
            'name' => 'New Payment',
            'code' => 'cash',
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('code');
});

it('requires max 80 characters for payment method name', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('payment-methods.store'), [
            'name' => str_repeat('A', 81),
            'code' => 'test',
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('name');
});

it('requires max 20 characters for payment method code', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('payment-methods.store'), [
            'name' => 'Test',
            'code' => str_repeat('A', 21),
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('code');
});

it('redirects guests from store payment method', function (): void {
    $response = $this->post(route('payment-methods.store'), [
        'name' => 'Cash',
        'code' => 'cash',
        'is_active' => true,
    ]);

    $response->assertRedirectToRoute('login');
});

it('renders edit payment method page', function (): void {
    $user = User::factory()->create();
    $method = PaymentMethod::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('payment-methods.edit', $method));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('payment-method/edit')
            ->has('method'));
});

it('redirects guests from edit payment method page', function (): void {
    $method = PaymentMethod::factory()->create();

    $response = $this->get(route('payment-methods.edit', $method));

    $response->assertRedirectToRoute('login');
});

it('may update a payment method', function (): void {
    $user = User::factory()->create();
    $method = PaymentMethod::factory()->create(['name' => 'Old Name', 'code' => 'old_code']);

    $response = $this->actingAs($user)
        ->fromRoute('payment-methods.edit', $method)
        ->put(route('payment-methods.update', $method), [
            'name' => 'New Name',
            'code' => 'new_code',
            'is_active' => false,
        ]);

    $response->assertRedirectToRoute('payment-methods.index');

    expect($method->refresh()->name)->toBe('New Name')
        ->and($method->code)->toBe('new_code')
        ->and($method->is_active)->toBeFalse();
});

it('requires name on payment method update', function (): void {
    $user = User::factory()->create();
    $method = PaymentMethod::factory()->create(['name' => 'Original Name']);

    $response = $this->actingAs($user)
        ->fromRoute('payment-methods.edit', $method)
        ->put(route('payment-methods.update', $method), [
            'code' => 'new_code',
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('name');

    expect($method->refresh()->name)->toBe('Original Name');
});

it('requires code on payment method update', function (): void {
    $user = User::factory()->create();
    $method = PaymentMethod::factory()->create(['code' => 'original']);

    $response = $this->actingAs($user)
        ->fromRoute('payment-methods.edit', $method)
        ->put(route('payment-methods.update', $method), [
            'name' => 'New Name',
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('code');

    expect($method->refresh()->code)->toBe('original');
});

it('allows updating payment method to same name', function (): void {
    $user = User::factory()->create();
    $method = PaymentMethod::factory()->create(['name' => 'Cash']);

    $response = $this->actingAs($user)
        ->fromRoute('payment-methods.edit', $method)
        ->put(route('payment-methods.update', $method), [
            'name' => 'Cash',
            'code' => 'cash',
            'is_active' => false,
        ]);

    $response->assertRedirectToRoute('payment-methods.index')
        ->assertSessionDoesntHaveErrors();

    expect($method->refresh()->is_active)->toBeFalse();
});

it('prevents updating to existing payment method name', function (): void {
    $user = User::factory()->create();
    PaymentMethod::factory()->create(['name' => 'Cash', 'code' => 'cash']);
    $method = PaymentMethod::factory()->create(['name' => 'Original']);

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->put(route('payment-methods.update', $method), [
            'name' => 'Cash',
            'code' => 'new_code',
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('name');
});

it('prevents updating to existing payment method code', function (): void {
    $user = User::factory()->create();
    PaymentMethod::factory()->create(['name' => 'Cash', 'code' => 'cash']);
    $method = PaymentMethod::factory()->create(['name' => 'Original']);

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->put(route('payment-methods.update', $method), [
            'name' => 'New',
            'code' => 'cash',
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('code');
});

it('redirects guests from update payment method', function (): void {
    $method = PaymentMethod::factory()->create();

    $response = $this->put(route('payment-methods.update', $method), [
        'name' => 'New Name',
        'code' => 'new_code',
        'is_active' => true,
    ]);

    $response->assertRedirectToRoute('login');
});

it('may delete a payment method', function (): void {
    $user = User::factory()->create();
    $method = PaymentMethod::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('payment-methods.index')
        ->delete(route('payment-methods.destroy', $method));

    $response->assertRedirectToRoute('payment-methods.index');

    expect($method->fresh())->toBeNull();
});

it('prevents deleting payment method with associated payments', function (): void {
    $user = User::factory()->create();
    $method = PaymentMethod::factory()->create();
    $warehouse = App\Models\Warehouse::factory()->create();
    $customer = App\Models\Customer::factory()->create();
    $sale = App\Models\Sale::factory()->create([
        'warehouse_id' => $warehouse->id,
        'customer_id' => $customer->id,
        'user_id' => $user->id,
    ]);
    App\Models\Payment::factory()->create([
        'payment_method_id' => $method->id,
        'payable_id' => $sale->id,
        'payable_type' => App\Models\Sale::class,
    ]);

    $response = $this->actingAs($user)
        ->fromRoute('payment-methods.index')
        ->delete(route('payment-methods.destroy', $method));

    $response->assertRedirectToRoute('payment-methods.index')
        ->assertSessionHas('error');

    expect($method->fresh())->not->toBeNull();
});

it('redirects guests from delete payment method', function (): void {
    $method = PaymentMethod::factory()->create();

    $response = $this->delete(route('payment-methods.destroy', $method));

    $response->assertRedirectToRoute('login');
});
