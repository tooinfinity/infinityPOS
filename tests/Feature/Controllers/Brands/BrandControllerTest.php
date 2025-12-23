<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may list brands', function (): void {
    Brand::factory()->count(5)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('brands.index'));

    $response->assertStatus(200); // View not created yet
});

it('may create a brand', function (): void {
    $response = $this->post(route('brands.store'), [
        'name' => 'Apple',
        'is_active' => true,
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect(route('brands.index'));

    $this->assertDatabaseHas('brands', [
        'name' => 'Apple',
    ]);
});

it('may update a brand', function (): void {
    $brand = Brand::factory()->create([
        'name' => 'Old Name',
        'created_by' => $this->user->id,
    ]);

    $response = $this->patch(route('brands.update', $brand), [
        'name' => 'Updated Name',
        'is_active' => null,
        'updated_by' => $this->user->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('brands', [
        'id' => $brand->id,
        'name' => 'Updated Name',
    ]);
});

it('may delete a brand', function (): void {
    $brand = Brand::factory()->create(['created_by' => $this->user->id]);

    $response = $this->delete(route('brands.destroy', $brand));

    $response->assertRedirect(route('brands.index'));

    $this->assertDatabaseMissing('brands', [
        'id' => $brand->id,
    ]);
});

it('may show create brand page', function (): void {
    $response = $this->get(route('brands.create'));

    $response->assertStatus(200); // View not created yet
});

it('may show a brand', function (): void {
    $brand = Brand::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('brands.show', $brand));

    $response->assertStatus(200); // View not created yet
});

it('may show edit brand page', function (): void {
    $brand = Brand::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('brands.edit', $brand));

    $response->assertStatus(200); // View not created yet
});
