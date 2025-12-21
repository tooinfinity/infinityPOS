<?php

declare(strict_types=1);

use App\Models\Invoice;
use App\Models\Sale;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may generate a sale invoice', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('sales.invoices.store', $sale), [
        'reference' => 'INV-001',
        'issued_at' => now()->toDateString(),
        'due_at' => now()->addDays(30)->toDateString(),
        'notes' => 'Payment due in 30 days',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('invoices', [
        'reference' => 'INV-001',
        'sale_id' => $sale->id,
    ]);
});

it('cannot generate invoice if one already exists', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);
    Invoice::factory()->create(['sale_id' => $sale->id]);

    $response = $this->post(route('sales.invoices.store', $sale), [
        'reference' => 'INV-002',
        'issued_at' => now()->toDateString(),
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['message']);
});

it('validates required invoice fields', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('sales.invoices.store', $sale), [
        'reference' => null,
        'issued_at' => null,
    ]);

    $response->assertSessionHasErrors(['reference', 'issued_at']);
});

it('may generate invoice without due date', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('sales.invoices.store', $sale), [
        'reference' => 'INV-003',
        'issued_at' => now()->toDateString(),
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('invoices', [
        'reference' => 'INV-003',
        'sale_id' => $sale->id,
        'due_at' => null,
    ]);
});

it('validates due date must be after issued date', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('sales.invoices.store', $sale), [
        'reference' => 'INV-004',
        'issued_at' => now()->toDateString(),
        'due_at' => now()->subDay()->toDateString(),
    ]);

    $response->assertSessionHasErrors(['due_at']);
});

it('may generate invoice with notes', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('sales.invoices.store', $sale), [
        'reference' => 'INV-005',
        'issued_at' => now()->toDateString(),
        'notes' => 'Custom invoice notes',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('invoices', [
        'reference' => 'INV-005',
        'sale_id' => $sale->id,
        'notes' => 'Custom invoice notes',
    ]);
});

it('validates invalid date format and shows error', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('sales.invoices.store', $sale), [
        'reference' => 'INV-007',
        'issued_at' => 'not-a-valid-date',
    ]);

    $response->assertSessionHasErrors(['issued_at']);
});

it('requires authentication', function (): void {
    auth()->logout();

    $sale = Sale::factory()->create();

    $response = $this->post(route('sales.invoices.store', $sale), [
        'reference' => 'INV-006',
        'issued_at' => now()->toDateString(),
    ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('login'));
});
