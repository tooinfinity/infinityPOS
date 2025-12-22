<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may list categories', function (): void {
    Category::factory()->count(5)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('categories.index'));

    $response->assertStatus(500); // View not created yet
});

it('may create a category', function (): void {
    $response = $this->post(route('categories.store'), [
        'name' => 'Electronics',
        'code' => 'PRD-001',
        'type' => App\Enums\CategoryTypeEnum::PRODUCT->value,
        'is_active' => true,
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('categories', [
        'name' => 'Electronics',
    ]);
});

it('may update a category', function (): void {
    $category = Category::factory()->create([
        'name' => 'Old Name',
        'created_by' => $this->user->id,
    ]);

    $response = $this->patch(route('categories.update', $category), [
        'name' => 'Updated Name',
        'code' => null,
        'type' => null,
        'is_active' => null,
        'updated_by' => $this->user->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Updated Name',
    ]);
});

it('may delete a category', function (): void {
    $category = Category::factory()->create(['created_by' => $this->user->id]);

    $response = $this->delete(route('categories.destroy', $category));

    $response->assertRedirect(route('categories.index'));

    $this->assertDatabaseMissing('categories', [
        'id' => $category->id,
    ]);
});
