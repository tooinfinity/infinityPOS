<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Product;
use App\Models\User;

it('renders brands index page', function (): void {
    $user = User::factory()->create();
    Brand::factory()->count(3)->create();

    $response = $this->actingAs($user)
        ->get(route('brands.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('brands/index')
            ->has('brands.data')
            ->has('filters'));
});

it('renders brands index with search filter', function (): void {
    $user = User::factory()->create();
    Brand::factory()->create(['name' => 'Apple']);
    Brand::factory()->create(['name' => 'Samsung']);

    $response = $this->actingAs($user)
        ->get(route('brands.index', ['search' => 'Apple']));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('brands/index')
            ->has('brands.data', 1)
            ->where('filters.search', 'Apple'));
});

it('renders brands index with sorting', function (): void {
    $user = User::factory()->create();
    Brand::factory()->create(['name' => 'Zebra']);
    Brand::factory()->create(['name' => 'Apple']);

    $response = $this->actingAs($user)
        ->get(route('brands.index', ['sort' => 'name', 'direction' => 'asc']));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('brands/index')
            ->where('filters.sort', 'name')
            ->where('filters.direction', 'asc'));
});

it('redirects guests from brands index', function (): void {
    $response = $this->get(route('brands.index'));

    $response->assertRedirectToRoute('login');
});

it('renders create brand page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('brands.create'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page->component('brands/create'));
});

it('redirects guests from create brand page', function (): void {
    $response = $this->get(route('brands.create'));

    $response->assertRedirectToRoute('login');
});

it('may create a brand', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('brands.create')
        ->post(route('brands.store'), [
            'name' => 'Test Brand',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('brands.show', Brand::query()->first());

    $brand = Brand::query()->where('name', 'Test Brand')->first();
    expect($brand)->not->toBeNull()
        ->and($brand->name)->toBe('Test Brand')
        ->and($brand->is_active)->toBeTrue();
});

it('requires name to create brand', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('brands.create')
        ->post(route('brands.store'), [
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('brands.create')
        ->assertSessionHasErrors('name');

    expect(Brand::query()->count())->toBe(0);
});

it('requires min 3 characters for brand name', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('brands.create')
        ->post(route('brands.store'), [
            'name' => 'AB',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('brands.create')
        ->assertSessionHasErrors('name');
});

it('requires max 80 characters for brand name', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('brands.create')
        ->post(route('brands.store'), [
            'name' => str_repeat('A', 81),
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('brands.create')
        ->assertSessionHasErrors('name');
});

it('requires unique brand name', function (): void {
    $user = User::factory()->create();
    Brand::factory()->create(['name' => 'Apple']);

    $response = $this->actingAs($user)
        ->fromRoute('brands.create')
        ->post(route('brands.store'), [
            'name' => 'Apple',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('brands.create')
        ->assertSessionHasErrors('name');

    expect(Brand::query()->count())->toBe(1);
});

it('redirects guests from store brand', function (): void {
    $response = $this->post(route('brands.store'), [
        'name' => 'Test Brand',
        'is_active' => true,
    ]);

    $response->assertRedirectToRoute('login');
});

it('renders show brand page', function (): void {
    $user = User::factory()->create();
    $brand = Brand::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('brands.show', $brand));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('brands/show')
            ->has('brand'));
});

it('includes product count on show page', function (): void {
    $user = User::factory()->create();
    $brand = Brand::factory()->create();
    Product::factory()->count(3)->create(['brand_id' => $brand->id]);

    $response = $this->actingAs($user)
        ->get(route('brands.show', $brand));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('brands/show')
            ->where('brand.products_count', 3));
});

it('redirects guests from show brand page', function (): void {
    $brand = Brand::factory()->create();

    $response = $this->get(route('brands.show', $brand));

    $response->assertRedirectToRoute('login');
});

it('renders edit brand page', function (): void {
    $user = User::factory()->create();
    $brand = Brand::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('brands.edit', $brand));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('brands/edit')
            ->has('brand'));
});

it('redirects guests from edit brand page', function (): void {
    $brand = Brand::factory()->create();

    $response = $this->get(route('brands.edit', $brand));

    $response->assertRedirectToRoute('login');
});

it('may update a brand', function (): void {
    $user = User::factory()->create();
    $brand = Brand::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($user)
        ->fromRoute('brands.edit', $brand)
        ->put(route('brands.update', $brand), [
            'name' => 'New Name',
            'is_active' => false,
        ]);

    $response->assertRedirectToRoute('brands.index');

    expect($brand->refresh()->name)->toBe('New Name')
        ->and($brand->is_active)->toBeFalse();
});

it('requires name on brand update', function (): void {
    $user = User::factory()->create();
    $brand = Brand::factory()->create(['name' => 'Original Name']);

    $response = $this->actingAs($user)
        ->fromRoute('brands.edit', $brand)
        ->put(route('brands.update', $brand), [
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('brands.edit', $brand)
        ->assertSessionHasErrors('name');

    expect($brand->refresh()->name)->toBe('Original Name');
});

it('allows updating brand to same name', function (): void {
    $user = User::factory()->create();
    $brand = Brand::factory()->create(['name' => 'Same Name']);

    $response = $this->actingAs($user)
        ->fromRoute('brands.edit', $brand)
        ->put(route('brands.update', $brand), [
            'name' => 'Same Name',
            'is_active' => false,
        ]);

    $response->assertRedirectToRoute('brands.index')
        ->assertSessionDoesntHaveErrors();

    expect($brand->refresh()->is_active)->toBeFalse();
});

it('prevents updating to existing brand name', function (): void {
    $user = User::factory()->create();
    Brand::factory()->create(['name' => 'Existing Name']);
    $brand = Brand::factory()->create(['name' => 'Original Name']);

    $response = $this->actingAs($user)
        ->fromRoute('brands.edit', $brand)
        ->put(route('brands.update', $brand), [
            'name' => 'Existing Name',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('brands.edit', $brand)
        ->assertSessionHasErrors('name');
});

it('redirects guests from update brand', function (): void {
    $brand = Brand::factory()->create();

    $response = $this->put(route('brands.update', $brand), [
        'name' => 'New Name',
        'is_active' => true,
    ]);

    $response->assertRedirectToRoute('login');
});

it('may delete a brand', function (): void {
    $user = User::factory()->create();
    $brand = Brand::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('brands.index')
        ->delete(route('brands.destroy', $brand));

    $response->assertRedirectToRoute('brands.index');

    expect($brand->fresh())->toBeNull();
});

it('may delete brand with associated products', function (): void {
    $user = User::factory()->create();
    $brand = Brand::factory()->create();
    Product::factory()->count(3)->create(['brand_id' => $brand->id]);

    $response = $this->actingAs($user)
        ->fromRoute('brands.index')
        ->delete(route('brands.destroy', $brand));

    $response->assertRedirectToRoute('brands.index');

    expect($brand->fresh())->toBeNull();
    expect(Product::query()->whereNotNull('brand_id')->count())->toBe(0);
});

it('redirects guests from delete brand', function (): void {
    $brand = Brand::factory()->create();

    $response = $this->delete(route('brands.destroy', $brand));

    $response->assertRedirectToRoute('login');
});
