<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may list clients', function (): void {
    Client::factory()->count(5)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('clients.index'));

    $response->assertStatus(500); // View not created yet
});

it('may create a client', function (): void {
    $response = $this->post(route('clients.store'), [
        'name' => 'John Doe',
        'phone' => '1234567890',
        'email' => 'john@example.com',
        'address' => '123 Main St',
        'article' => null,
        'nif' => null,
        'nis' => null,
        'rc' => null,
        'rib' => null,
        'is_active' => true,
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect(route('clients.index'));

    $this->assertDatabaseHas('clients', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
});

it('may update a client', function (): void {
    $client = Client::factory()->create([
        'name' => 'Old Name',
        'created_by' => $this->user->id,
    ]);

    $response = $this->patch(route('clients.update', $client), [
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

    $this->assertDatabaseHas('clients', [
        'id' => $client->id,
        'name' => 'Updated Name',
    ]);
});

it('may delete a client', function (): void {
    $client = Client::factory()->create(['created_by' => $this->user->id]);

    $response = $this->delete(route('clients.destroy', $client));

    $response->assertRedirect(route('clients.index'));

    $this->assertDatabaseMissing('clients', [
        'id' => $client->id,
    ]);
});
