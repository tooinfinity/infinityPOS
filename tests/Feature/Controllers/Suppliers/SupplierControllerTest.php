<?php

declare(strict_types=1);

use App\Models\Supplier;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may list suppliers', function (): void {
    Supplier::factory()->count(5)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('suppliers.index'));

    $response->assertStatus(200); // View not created yet
});

it('may create a supplier', function (): void {
    $response = $this->post(route('suppliers.store'), [
        'name' => 'ACME Corp',
        'phone' => '9876543210',
        'email' => 'acme@example.com',
        'address' => '456 Supply St',
        'article' => null,
        'nif' => null,
        'nis' => null,
        'rc' => null,
        'rib' => null,
        'is_active' => true,
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect(route('suppliers.index'));

    $this->assertDatabaseHas('suppliers', [
        'name' => 'ACME Corp',
        'email' => 'acme@example.com',
    ]);
});

it('may update a supplier', function (): void {
    $supplier = Supplier::factory()->create([
        'name' => 'Old Name',
        'created_by' => $this->user->id,
    ]);

    $response = $this->patch(route('suppliers.update', $supplier), [
        'name' => 'Updated Name',
        'phone' => null,
        'email' => null,
        'address' => null,
        'article' => null,
        'nif' => null,
        'nis' => null,
        'rc' => null,
        'rib' => null,
        'is_active' => null,
        'updated_by' => $this->user->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('suppliers', [
        'id' => $supplier->id,
        'name' => 'Updated Name',
    ]);
});

it('may delete a supplier', function (): void {
    $supplier = Supplier::factory()->create(['created_by' => $this->user->id]);

    $response = $this->delete(route('suppliers.destroy', $supplier));

    $response->assertRedirect(route('suppliers.index'));

    $this->assertDatabaseMissing('suppliers', [
        'id' => $supplier->id,
    ]);
});

it('may show create supplier page', function (): void {
    $response = $this->get(route('suppliers.create'));

    $response->assertStatus(200); // View not created yet
});

it('may show a supplier', function (): void {
    $supplier = Supplier::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('suppliers.show', $supplier));

    $response->assertStatus(200); // View not created yet
});

it('may show edit supplier page', function (): void {
    $supplier = Supplier::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('suppliers.edit', $supplier));

    $response->assertStatus(200); // View not created yet
});
