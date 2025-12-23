<?php

declare(strict_types=1);

use App\Models\Store;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may list stores', function (): void {
    Store::factory()->count(5)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('stores.index'));

    $response->assertStatus(200); // View not created yet
});

it('may create a store', function (): void {
    $response = $this->post(route('stores.store'), [
        'name' => 'Main Store',
        'city' => 'Chicago',
        'address' => '789 Store Ave',
        'phone' => '5555555555',
        'is_active' => true,
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect(route('stores.index'));

    $this->assertDatabaseHas('stores', [
        'name' => 'Main Store',
        'city' => 'Chicago',
    ]);
});

it('may update a store', function (): void {
    $store = Store::factory()->create([
        'name' => 'Old Name',
        'created_by' => $this->user->id,
    ]);

    $response = $this->patch(route('stores.update', $store), [
        'name' => 'Updated Name',
        'city' => null,
        'address' => null,
        'phone' => null,
        'is_active' => null,
        'updated_by' => $this->user->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('stores', [
        'id' => $store->id,
        'name' => 'Updated Name',
    ]);
});

it('may delete a store', function (): void {
    $store = Store::factory()->create(['created_by' => $this->user->id]);

    $response = $this->delete(route('stores.destroy', $store));

    $response->assertRedirect(route('stores.index'));

    $this->assertDatabaseMissing('stores', [
        'id' => $store->id,
    ]);
});

it('may show create store page', function (): void {
    $response = $this->get(route('stores.create'));

    $response->assertStatus(200); // View not created yet
});

it('may show a store', function (): void {
    $store = Store::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('stores.show', $store));

    $response->assertStatus(200); // View not created yet
});

it('may show edit store page', function (): void {
    $store = Store::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('stores.edit', $store));

    $response->assertStatus(200); // View not created yet
});
