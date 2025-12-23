<?php

declare(strict_types=1);

use App\Models\Unit;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may list units', function (): void {
    Unit::factory()->count(5)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('units.index'));

    $response->assertStatus(200); // View not created yet
});

it('may create a unit', function (): void {
    $response = $this->post(route('units.store'), [
        'name' => 'Kilogram',
        'short_name' => 'kg',
        'is_active' => true,
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect(route('units.index'));

    $this->assertDatabaseHas('units', [
        'name' => 'Kilogram',
        'short_name' => 'kg',
    ]);
});

it('may update a unit', function (): void {
    $unit = Unit::factory()->create([
        'name' => 'Old Name',
        'created_by' => $this->user->id,
    ]);

    $response = $this->patch(route('units.update', $unit), [
        'name' => 'Updated Name',
        'short_name' => null,
        'is_active' => null,
        'updated_by' => $this->user->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('units', [
        'id' => $unit->id,
        'name' => 'Updated Name',
    ]);
});

it('may delete a unit', function (): void {
    $unit = Unit::factory()->create(['created_by' => $this->user->id]);

    $response = $this->delete(route('units.destroy', $unit));

    $response->assertRedirect(route('units.index'));

    $this->assertDatabaseMissing('units', [
        'id' => $unit->id,
    ]);
});

it('may show create unit page', function (): void {
    $response = $this->get(route('units.create'));

    $response->assertStatus(200); // View not created yet
});

it('may show a unit', function (): void {
    $unit = Unit::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('units.show', $unit));

    $response->assertStatus(200); // View not created yet
});

it('may show edit unit page', function (): void {
    $unit = Unit::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('units.edit', $unit));

    $response->assertStatus(200); // View not created yet
});
