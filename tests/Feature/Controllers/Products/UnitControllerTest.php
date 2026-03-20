<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\Unit;
use App\Models\User;

it('renders units index page', function (): void {
    $user = User::factory()->create();
    Unit::factory()->count(3)->create();

    $response = $this->actingAs($user)
        ->get(route('units.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('units/index')
            ->has('units.data')
            ->has('filters'));
});

it('renders units index with search filter', function (): void {
    $user = User::factory()->create();
    Unit::factory()->create(['name' => 'Kilogram']);
    Unit::factory()->create(['name' => 'Liter']);

    $response = $this->actingAs($user)
        ->get(route('units.index', ['search' => 'Kilogram']));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('units/index')
            ->has('units.data', 1)
            ->where('filters.search', 'Kilogram'));
});

it('redirects guests from units index', function (): void {
    $response = $this->get(route('units.index'));

    $response->assertRedirectToRoute('login');
});

it('renders create unit page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('units.create'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page->component('units/create'));
});

it('redirects guests from create unit page', function (): void {
    $response = $this->get(route('units.create'));

    $response->assertRedirectToRoute('login');
});

it('may create a unit', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('units.create')
        ->post(route('units.store'), [
            'name' => 'Test Unit',
            'short_name' => 'TU',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('units.show', Unit::query()->first());

    $unit = Unit::query()->where('name', 'Test Unit')->first();
    expect($unit)->not->toBeNull()
        ->and($unit->name)->toBe('Test Unit')
        ->and($unit->short_name)->toBe('TU')
        ->and($unit->is_active)->toBeTrue();
});

it('requires name to create unit', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('units.create')
        ->post(route('units.store'), [
            'short_name' => 'TU',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('units.create')
        ->assertSessionHasErrors('name');

    expect(Unit::query()->count())->toBe(0);
});

it('requires short_name to create unit', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('units.create')
        ->post(route('units.store'), [
            'name' => 'Test Unit',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('units.create')
        ->assertSessionHasErrors('short_name');

    expect(Unit::query()->count())->toBe(0);
});

it('requires min 3 characters for unit name', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('units.create')
        ->post(route('units.store'), [
            'name' => 'AB',
            'short_name' => 'AB',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('units.create')
        ->assertSessionHasErrors('name');
});

it('requires min 1 character for short_name', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('units.create')
        ->post(route('units.store'), [
            'name' => 'Test Unit',
            'short_name' => '',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('units.create')
        ->assertSessionHasErrors('short_name');
});

it('requires max 20 characters for short_name', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('units.create')
        ->post(route('units.store'), [
            'name' => 'Test Unit',
            'short_name' => str_repeat('A', 21),
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('units.create')
        ->assertSessionHasErrors('short_name');
});

it('requires unique unit name', function (): void {
    $user = User::factory()->create();
    Unit::factory()->create(['name' => 'Kilogram']);

    $response = $this->actingAs($user)
        ->fromRoute('units.create')
        ->post(route('units.store'), [
            'name' => 'Kilogram',
            'short_name' => 'kg',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('units.create')
        ->assertSessionHasErrors('name');

    expect(Unit::query()->count())->toBe(1);
});

it('redirects guests from store unit', function (): void {
    $response = $this->post(route('units.store'), [
        'name' => 'Test Unit',
        'short_name' => 'TU',
        'is_active' => true,
    ]);

    $response->assertRedirectToRoute('login');
});

it('renders show unit page', function (): void {
    $user = User::factory()->create();
    $unit = Unit::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('units.show', $unit));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('units/show')
            ->has('unit'));
});

it('includes product count on show page', function (): void {
    $user = User::factory()->create();
    $unit = Unit::factory()->create();
    Product::factory()->count(3)->create(['unit_id' => $unit->id]);

    $response = $this->actingAs($user)
        ->get(route('units.show', $unit));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('units/show')
            ->where('unit.products_count', 3));
});

it('redirects guests from show unit page', function (): void {
    $unit = Unit::factory()->create();

    $response = $this->get(route('units.show', $unit));

    $response->assertRedirectToRoute('login');
});

it('renders edit unit page', function (): void {
    $user = User::factory()->create();
    $unit = Unit::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('units.edit', $unit));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('units/edit')
            ->has('unit'));
});

it('redirects guests from edit unit page', function (): void {
    $unit = Unit::factory()->create();

    $response = $this->get(route('units.edit', $unit));

    $response->assertRedirectToRoute('login');
});

it('may update a unit', function (): void {
    $user = User::factory()->create();
    $unit = Unit::factory()->create(['name' => 'Old Name', 'short_name' => 'ON']);

    $response = $this->actingAs($user)
        ->fromRoute('units.edit', $unit)
        ->put(route('units.update', $unit), [
            'name' => 'New Name',
            'short_name' => 'NN',
            'is_active' => false,
        ]);

    $response->assertRedirectToRoute('units.index');

    expect($unit->refresh()->name)->toBe('New Name')
        ->and($unit->short_name)->toBe('NN')
        ->and($unit->is_active)->toBeFalse();
});

it('requires name on unit update', function (): void {
    $user = User::factory()->create();
    $unit = Unit::factory()->create(['name' => 'Original Name']);

    $response = $this->actingAs($user)
        ->fromRoute('units.edit', $unit)
        ->put(route('units.update', $unit), [
            'short_name' => 'TU',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('units.edit', $unit)
        ->assertSessionHasErrors('name');

    expect($unit->refresh()->name)->toBe('Original Name');
});

it('requires short_name on unit update', function (): void {
    $user = User::factory()->create();
    $unit = Unit::factory()->create(['name' => 'Original Name', 'short_name' => 'ON']);

    $response = $this->actingAs($user)
        ->fromRoute('units.edit', $unit)
        ->put(route('units.update', $unit), [
            'name' => 'New Name',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('units.edit', $unit)
        ->assertSessionHasErrors('short_name');

    expect($unit->refresh()->short_name)->toBe('ON');
});

it('allows updating unit to same name', function (): void {
    $user = User::factory()->create();
    $unit = Unit::factory()->create(['name' => 'Same Name']);

    $response = $this->actingAs($user)
        ->fromRoute('units.edit', $unit)
        ->put(route('units.update', $unit), [
            'name' => 'Same Name',
            'short_name' => 'SN',
            'is_active' => false,
        ]);

    $response->assertRedirectToRoute('units.index')
        ->assertSessionDoesntHaveErrors();

    expect($unit->refresh()->is_active)->toBeFalse();
});

it('prevents updating to existing unit name', function (): void {
    $user = User::factory()->create();
    Unit::factory()->create(['name' => 'Existing Name']);
    $unit = Unit::factory()->create(['name' => 'Original Name']);

    $response = $this->actingAs($user)
        ->fromRoute('units.edit', $unit)
        ->put(route('units.update', $unit), [
            'name' => 'Existing Name',
            'short_name' => 'EN',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('units.edit', $unit)
        ->assertSessionHasErrors('name');
});

it('redirects guests from update unit', function (): void {
    $unit = Unit::factory()->create();

    $response = $this->put(route('units.update', $unit), [
        'name' => 'New Name',
        'short_name' => 'NN',
        'is_active' => true,
    ]);

    $response->assertRedirectToRoute('login');
});

it('may delete a unit', function (): void {
    $user = User::factory()->create();
    $unit = Unit::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('units.index')
        ->delete(route('units.destroy', $unit));

    $response->assertRedirectToRoute('units.index');

    expect($unit->fresh())->toBeNull();
});

it('may delete unit with associated products', function (): void {
    $user = User::factory()->create();
    Unit::factory()->piece()->create();
    $unit = Unit::factory()->create();
    Product::factory()->count(3)->create(['unit_id' => $unit->id]);

    $response = $this->actingAs($user)
        ->fromRoute('units.index')
        ->delete(route('units.destroy', $unit));

    $response->assertRedirectToRoute('units.index');

    expect($unit->fresh())->toBeNull();
});

it('redirects guests from delete unit', function (): void {
    $unit = Unit::factory()->create();

    $response = $this->delete(route('units.destroy', $unit));

    $response->assertRedirectToRoute('login');
});
