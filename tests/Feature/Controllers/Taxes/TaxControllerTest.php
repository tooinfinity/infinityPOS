<?php

declare(strict_types=1);

use App\Models\Tax;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may list taxes', function (): void {
    Tax::factory()->count(5)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('taxes.index'));

    $response->assertStatus(200); // View not created yet
});

it('may create a tax', function (): void {
    $response = $this->post(route('taxes.store'), [
        'name' => 'VAT',
        'rate' => 15,
        'tax_type' => App\Enums\TaxTypeEnum::PERCENTAGE->value,
        'is_active' => true,
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect(route('taxes.index'));

    $this->assertDatabaseHas('taxes', [
        'name' => 'VAT',
        'rate' => 15.00,
    ]);
});

it('may update a tax', function (): void {
    $tax = Tax::factory()->create([
        'name' => 'Old Tax',
        'rate' => 10.00,
        'created_by' => $this->user->id,
    ]);

    $response = $this->patch(route('taxes.update', $tax), [
        'name' => 'Updated Tax',
        'rate' => 20,
        'tax_type' => null,
        'is_active' => null,
        'updated_by' => $this->user->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('taxes', [
        'id' => $tax->id,
        'name' => 'Updated Tax',
        'rate' => 20.00,
    ]);
});

it('may delete a tax', function (): void {
    $tax = Tax::factory()->create(['created_by' => $this->user->id]);

    $response = $this->delete(route('taxes.destroy', $tax));

    $response->assertRedirect(route('taxes.index'));

    $this->assertDatabaseMissing('taxes', [
        'id' => $tax->id,
    ]);
});

it('may show create tax page', function (): void {
    $response = $this->get(route('taxes.create'));

    $response->assertStatus(200); // View not created yet
});

it('may show a tax', function (): void {
    $tax = Tax::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('taxes.show', $tax));

    $response->assertStatus(200); // View not created yet
});

it('may show edit tax page', function (): void {
    $tax = Tax::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('taxes.edit', $tax));

    $response->assertStatus(200); // View not created yet
});
