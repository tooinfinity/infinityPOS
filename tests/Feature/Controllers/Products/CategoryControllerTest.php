<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

it('renders categories index page', function (): void {
    $user = User::factory()->create();
    Category::factory()->count(3)->create();

    $response = $this->actingAs($user)
        ->get(route('categories.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('categories/index')
            ->has('categories.data')
            ->has('filters'));
});

it('renders categories index with search filter', function (): void {
    $user = User::factory()->create();
    Category::factory()->create(['name' => 'Electronics']);
    Category::factory()->create(['name' => 'Clothing']);

    $response = $this->actingAs($user)
        ->get(route('categories.index', ['search' => 'Electronics']));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('categories/index')
            ->has('categories.data', 1)
            ->where('filters.search', 'Electronics'));
});

it('redirects guests from categories index', function (): void {
    $response = $this->get(route('categories.index'));

    $response->assertRedirectToRoute('login');
});

it('renders create category page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('categories.create'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page->component('categories/create'));
});

it('redirects guests from create category page', function (): void {
    $response = $this->get(route('categories.create'));

    $response->assertRedirectToRoute('login');
});

it('may create a category', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('categories.create')
        ->post(route('categories.store'), [
            'name' => 'Test Category',
            'description' => 'Test description',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('categories.show', Category::query()->first());

    $category = Category::query()->where('name', 'Test Category')->first();
    expect($category)->not->toBeNull()
        ->and($category->name)->toBe('Test Category')
        ->and($category->description)->toBe('Test description')
        ->and($category->is_active)->toBeTrue();
});

it('requires name to create category', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('categories.create')
        ->post(route('categories.store'), [
            'description' => 'Test description',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('categories.create')
        ->assertSessionHasErrors('name');

    expect(Category::query()->count())->toBe(0);
});

it('requires min 3 characters for category name', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('categories.create')
        ->post(route('categories.store'), [
            'name' => 'AB',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('categories.create')
        ->assertSessionHasErrors('name');
});

it('requires max 80 characters for category name', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('categories.create')
        ->post(route('categories.store'), [
            'name' => str_repeat('A', 81),
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('categories.create')
        ->assertSessionHasErrors('name');
});

it('requires unique category name', function (): void {
    $user = User::factory()->create();
    Category::factory()->create(['name' => 'Electronics']);

    $response = $this->actingAs($user)
        ->fromRoute('categories.create')
        ->post(route('categories.store'), [
            'name' => 'Electronics',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('categories.create')
        ->assertSessionHasErrors('name');

    expect(Category::query()->count())->toBe(1);
});

it('allows creating category without description', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('categories.create')
        ->post(route('categories.store'), [
            'name' => 'Test Category',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('categories.show', Category::query()->first())
        ->assertSessionDoesntHaveErrors();

    $category = Category::query()->where('name', 'Test Category')->first();
    expect($category)->not->toBeNull()
        ->and($category->description)->toBeNull();
});

it('redirects guests from store category', function (): void {
    $response = $this->post(route('categories.store'), [
        'name' => 'Test Category',
        'is_active' => true,
    ]);

    $response->assertRedirectToRoute('login');
});

it('renders show category page', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('categories.show', $category));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('categories/show')
            ->has('category'));
});

it('includes product count on show page', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    Product::factory()->count(3)->create(['category_id' => $category->id]);

    $response = $this->actingAs($user)
        ->get(route('categories.show', $category));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('categories/show')
            ->where('category.products_count', 3));
});

it('redirects guests from show category page', function (): void {
    $category = Category::factory()->create();

    $response = $this->get(route('categories.show', $category));

    $response->assertRedirectToRoute('login');
});

it('renders edit category page', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('categories.edit', $category));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('categories/edit')
            ->has('category'));
});

it('redirects guests from edit category page', function (): void {
    $category = Category::factory()->create();

    $response = $this->get(route('categories.edit', $category));

    $response->assertRedirectToRoute('login');
});

it('may update a category', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($user)
        ->fromRoute('categories.edit', $category)
        ->put(route('categories.update', $category), [
            'name' => 'New Name',
            'description' => 'New description',
            'is_active' => false,
        ]);

    $response->assertRedirectToRoute('categories.index');

    expect($category->refresh()->name)->toBe('New Name')
        ->and($category->description)->toBe('New description')
        ->and($category->is_active)->toBeFalse();
});

it('requires name on category update', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Original Name']);

    $response = $this->actingAs($user)
        ->fromRoute('categories.edit', $category)
        ->put(route('categories.update', $category), [
            'description' => 'New description',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('categories.edit', $category)
        ->assertSessionHasErrors('name');

    expect($category->refresh()->name)->toBe('Original Name');
});

it('allows updating category to same name', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Same Name']);

    $response = $this->actingAs($user)
        ->fromRoute('categories.edit', $category)
        ->put(route('categories.update', $category), [
            'name' => 'Same Name',
            'is_active' => false,
        ]);

    $response->assertRedirectToRoute('categories.index')
        ->assertSessionDoesntHaveErrors();

    expect($category->refresh()->is_active)->toBeFalse();
});

it('prevents updating to existing category name', function (): void {
    $user = User::factory()->create();
    Category::factory()->create(['name' => 'Existing Name']);
    $category = Category::factory()->create(['name' => 'Original Name']);

    $response = $this->actingAs($user)
        ->fromRoute('categories.edit', $category)
        ->put(route('categories.update', $category), [
            'name' => 'Existing Name',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('categories.edit', $category)
        ->assertSessionHasErrors('name');
});

it('redirects guests from update category', function (): void {
    $category = Category::factory()->create();

    $response = $this->put(route('categories.update', $category), [
        'name' => 'New Name',
        'is_active' => true,
    ]);

    $response->assertRedirectToRoute('login');
});

it('may delete a category', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('categories.index')
        ->delete(route('categories.destroy', $category));

    $response->assertRedirectToRoute('categories.index');

    expect($category->fresh())->toBeNull();
});

it('may delete category with associated products', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    Product::factory()->count(3)->create(['category_id' => $category->id]);

    $response = $this->actingAs($user)
        ->fromRoute('categories.index')
        ->delete(route('categories.destroy', $category));

    $response->assertRedirectToRoute('categories.index');

    expect($category->fresh())->toBeNull();
    expect(Product::query()->whereNotNull('category_id')->count())->toBe(0);
});

it('redirects guests from delete category', function (): void {
    $category = Category::factory()->create();

    $response = $this->delete(route('categories.destroy', $category));

    $response->assertRedirectToRoute('login');
});
