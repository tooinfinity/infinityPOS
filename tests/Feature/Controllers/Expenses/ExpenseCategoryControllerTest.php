<?php

declare(strict_types=1);

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;

it('renders expense categories index page', function (): void {
    $user = User::factory()->create();
    ExpenseCategory::factory()->count(3)->create();

    $response = $this->actingAs($user)
        ->get(route('expense-categories.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('expense-category/index')
            ->has('categories.data')
            ->has('filters'));
});

it('renders expense categories index with search filter', function (): void {
    $user = User::factory()->create();
    ExpenseCategory::factory()->create(['name' => 'Office Supplies']);
    ExpenseCategory::factory()->create(['name' => 'Travel']);

    $response = $this->actingAs($user)
        ->get(route('expense-categories.index', ['search' => 'Office']));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('expense-category/index')
            ->has('categories.data', 1)
            ->where('filters.search', 'Office'));
});

it('redirects guests from expense categories index', function (): void {
    $response = $this->get(route('expense-categories.index'));

    $response->assertRedirectToRoute('login');
});

it('renders create expense category page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('expense-categories.create'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page->component('expense-category/create'));
});

it('redirects guests from create expense category page', function (): void {
    $response = $this->get(route('expense-categories.create'));

    $response->assertRedirectToRoute('login');
});

it('may create an expense category', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('expense-categories.store'), [
            'name' => 'Office Supplies',
            'description' => 'All office supply expenses',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('expense-categories.index');

    $category = ExpenseCategory::query()->where('name', 'Office Supplies')->first();
    expect($category)->not->toBeNull()
        ->and($category->name)->toBe('Office Supplies')
        ->and($category->description)->toBe('All office supply expenses')
        ->and($category->is_active)->toBeTrue();
});

it('requires name to create expense category', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('expense-categories.store'), [
            'description' => 'Test description',
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('name');

    expect(ExpenseCategory::query()->count())->toBe(0);
});

it('requires min 3 characters for expense category name', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('expense-categories.store'), [
            'name' => 'AB',
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('name');
});

it('requires max 80 characters for expense category name', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('expense-categories.store'), [
            'name' => str_repeat('A', 81),
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('name');
});

it('requires unique expense category name', function (): void {
    $user = User::factory()->create();
    ExpenseCategory::factory()->create(['name' => 'Office Supplies']);

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('expense-categories.store'), [
            'name' => 'Office Supplies',
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('name');

    expect(ExpenseCategory::query()->count())->toBe(1);
});

it('allows creating expense category without description', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('expense-categories.store'), [
            'name' => 'Office Supplies',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('expense-categories.index')
        ->assertSessionDoesntHaveErrors();

    $category = ExpenseCategory::query()->where('name', 'Office Supplies')->first();
    expect($category)->not->toBeNull()
        ->and($category->description)->toBeNull();
});

it('redirects guests from store expense category', function (): void {
    $response = $this->post(route('expense-categories.store'), [
        'name' => 'Office Supplies',
        'is_active' => true,
    ]);

    $response->assertRedirectToRoute('login');
});

it('renders edit expense category page', function (): void {
    $user = User::factory()->create();
    $category = ExpenseCategory::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('expense-categories.edit', $category));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('expense-category/edit')
            ->has('category'));
});

it('redirects guests from edit expense category page', function (): void {
    $category = ExpenseCategory::factory()->create();

    $response = $this->get(route('expense-categories.edit', $category));

    $response->assertRedirectToRoute('login');
});

it('may update an expense category', function (): void {
    $user = User::factory()->create();
    $category = ExpenseCategory::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($user)
        ->fromRoute('expense-categories.edit', $category)
        ->put(route('expense-categories.update', $category), [
            'name' => 'New Name',
            'description' => 'New description',
            'is_active' => false,
        ]);

    $response->assertRedirectToRoute('expense-categories.index');

    expect($category->refresh()->name)->toBe('New Name')
        ->and($category->description)->toBe('New description')
        ->and($category->is_active)->toBeFalse();
});

it('requires name on expense category update', function (): void {
    $user = User::factory()->create();
    $category = ExpenseCategory::factory()->create(['name' => 'Original Name']);

    $response = $this->actingAs($user)
        ->fromRoute('expense-categories.edit', $category)
        ->put(route('expense-categories.update', $category), [
            'description' => 'New description',
            'is_active' => true,
        ]);

    $response->assertRedirectToRoute('expense-categories.edit', $category)
        ->assertSessionHasErrors('name');

    expect($category->refresh()->name)->toBe('Original Name');
});

it('allows updating expense category to same name', function (): void {
    $user = User::factory()->create();
    $category = ExpenseCategory::factory()->create(['name' => 'Same Name']);

    $response = $this->actingAs($user)
        ->fromRoute('expense-categories.edit', $category)
        ->put(route('expense-categories.update', $category), [
            'name' => 'Same Name',
            'is_active' => false,
        ]);

    $response->assertRedirectToRoute('expense-categories.index')
        ->assertSessionDoesntHaveErrors();

    expect($category->refresh()->is_active)->toBeFalse();
});

it('prevents updating to existing expense category name', function (): void {
    $user = User::factory()->create();
    ExpenseCategory::factory()->create(['name' => 'Existing Name']);
    $category = ExpenseCategory::factory()->create(['name' => 'Original Name']);

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->put(route('expense-categories.update', $category), [
            'name' => 'Existing Name',
            'is_active' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHasErrors('name');
});

it('redirects guests from update expense category', function (): void {
    $category = ExpenseCategory::factory()->create();

    $response = $this->put(route('expense-categories.update', $category), [
        'name' => 'New Name',
        'is_active' => true,
    ]);

    $response->assertRedirectToRoute('login');
});

it('may delete an expense category', function (): void {
    $user = User::factory()->create();
    $category = ExpenseCategory::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('expense-categories.index')
        ->delete(route('expense-categories.destroy', $category));

    $response->assertRedirectToRoute('expense-categories.index');

    expect($category->fresh())->toBeNull();
});

it('prevents deleting expense category with associated expenses', function (): void {
    $user = User::factory()->create();
    $category = ExpenseCategory::factory()->create();
    Expense::factory()->create(['expense_category_id' => $category->id]);

    $response = $this->actingAs($user)
        ->fromRoute('expense-categories.index')
        ->delete(route('expense-categories.destroy', $category));

    $response->assertRedirectToRoute('expense-categories.index')
        ->assertSessionHas('error');

    expect($category->fresh())->not->toBeNull();
});

it('redirects guests from delete expense category', function (): void {
    $category = ExpenseCategory::factory()->create();

    $response = $this->delete(route('expense-categories.destroy', $category));

    $response->assertRedirectToRoute('login');
});
