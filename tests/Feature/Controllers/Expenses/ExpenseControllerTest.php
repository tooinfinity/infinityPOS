<?php

declare(strict_types=1);

use App\Enums\CategoryTypeEnum;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may list expenses', function (): void {
    Expense::factory()->count(5)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('expenses.index'));

    $response->assertStatus(200); // View not created yet
});

it('may create an expense', function (): void {
    $category = Category::factory()->create([
        'type' => CategoryTypeEnum::EXPENSE,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('expenses.store'), [
        'amount' => 50000,
        'description' => 'Office supplies',
        'category_id' => $category->id,
        'store_id' => null,
        'moneybox_id' => null,
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect(route('expenses.index'));

    $this->assertDatabaseHas('expenses', [
        'amount' => 50000,
        'description' => 'Office supplies',
        'category_id' => $category->id,
    ]);
});

it('may update an expense', function (): void {
    $expense = Expense::factory()->create([
        'created_by' => $this->user->id,
    ]);

    $response = $this->patch(route('expenses.update', $expense), [
        'amount' => 60000,
        'description' => 'Updated description',
        'category_id' => null,
        'store_id' => null,
        'moneybox_id' => null,
        'updated_by' => $this->user->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('expenses', [
        'id' => $expense->id,
        'amount' => 60000,
        'description' => 'Updated description',
    ]);
});

it('may delete an expense', function (): void {
    $expense = Expense::factory()->create(['created_by' => $this->user->id]);

    $response = $this->delete(route('expenses.destroy', $expense));

    $response->assertRedirect(route('expenses.index'));

    $this->assertDatabaseMissing('expenses', [
        'id' => $expense->id,
    ]);
});

it('may show create expense page', function (): void {
    $response = $this->get(route('expenses.create'));

    $response->assertStatus(200); // View not created yet
});

it('may show a expense', function (): void {
    $expense = Expense::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('expenses.show', $expense));

    $response->assertStatus(200); // View not created yet
});

it('may show edit expense page', function (): void {
    $expense = Expense::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('expenses.edit', $expense));

    $response->assertStatus(200); // View not created yet
});
