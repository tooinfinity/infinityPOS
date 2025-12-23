<?php

declare(strict_types=1);

use App\Enums\InvoiceStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\Log;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may list invoices', function (): void {
    Invoice::factory()->count(5)->create();

    $response = $this->get(route('invoices.index'));

    $response->assertStatus(200); // View not created yet
});

it('may show create invoice page', function (): void {
    $response = $this->get(route('invoices.create'));

    $response->assertStatus(200); // View not created yet
});

it('may generate an invoice', function (): void {
    $client = Client::factory()->create(['created_by' => $this->user->id]);
    $sale = Sale::factory()->create([
        'client_id' => $client->id,
        'subtotal' => 100000,
        'discount' => 5000,
        'tax' => 9500,
        'total' => 104500,
        'paid' => 0,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('invoices.store'), [
        'reference' => 'INV-001',
        'sale_id' => $sale->id,
        'client_id' => $client->id,
        'issued_at' => now()->toDateString(),
        'due_at' => now()->addDays(30)->toDateString(),
        'notes' => 'Test invoice',
        'created_by' => $this->user->id,
    ]);

    $invoice = Invoice::query()->where('reference', 'INV-001')->first();

    $response->assertRedirect(route('invoices.show', $invoice));

    $this->assertDatabaseHas('invoices', [
        'reference' => 'INV-001',
        'sale_id' => $sale->id,
        'status' => InvoiceStatusEnum::PENDING->value,
    ]);
});

it('cannot generate duplicate invoice for same sale', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);
    Invoice::factory()->create(['sale_id' => $sale->id]);

    $response = $this->post(route('invoices.store'), [
        'reference' => 'INV-DUP',
        'sale_id' => $sale->id,
        'client_id' => null,
        'issued_at' => now()->toDateString(),
        'due_at' => null,
        'notes' => null,
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['message']);
});

it('may show an invoice', function (): void {
    $invoice = Invoice::factory()->create();

    $response = $this->get(route('invoices.show', $invoice));

    $response->assertStatus(200); // View not created yet
});

it('may show edit invoice page', function (): void {
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::PENDING,
    ]);

    $response = $this->get(route('invoices.edit', $invoice));

    $response->assertStatus(200); // View not created yet
});

it('may update an invoice', function (): void {
    $invoice = Invoice::factory()->create([
        'reference' => 'INV-001',
        'status' => InvoiceStatusEnum::PENDING,
    ]);

    $response = $this->patch(route('invoices.update', $invoice), [
        'reference' => 'INV-001-UPDATED',
        'due_at' => null,
        'notes' => 'Updated notes',
        'updated_by' => $this->user->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('invoices', [
        'id' => $invoice->id,
        'reference' => 'INV-001-UPDATED',
        'notes' => 'Updated notes',
    ]);
});

it('cannot update a paid invoice', function (): void {
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::PAID,
    ]);

    $response = $this->patch(route('invoices.update', $invoice), [
        'reference' => 'INV-NEW',
        'due_at' => null,
        'notes' => null,
        'updated_by' => $this->user->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['message']);
});

it('may cancel an invoice', function (): void {
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::PENDING,
    ]);

    $response = $this->post(route('invoices.cancel', $invoice));

    $response->assertRedirect();

    $this->assertDatabaseHas('invoices', [
        'id' => $invoice->id,
        'status' => InvoiceStatusEnum::CANCELLED->value,
    ]);
});

it('cannot cancel a paid invoice', function (): void {
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::PAID,
    ]);

    $response = $this->post(route('invoices.cancel', $invoice));

    $response->assertRedirect();
    $response->assertSessionHasErrors(['message']);
});

it('may mark invoice as paid when fully paid', function (): void {
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::PENDING,
        'total' => 100000,
        'paid' => 0,
    ]);

    // Add payment to cover full amount
    Payment::factory()->create([
        'amount' => 100000,
        'method' => PaymentMethodEnum::CASH,
        'related_type' => Invoice::class,
        'related_id' => $invoice->id,
    ]);

    $response = $this->post(route('invoices.mark-as-paid', $invoice));

    $response->assertRedirect();

    $this->assertDatabaseHas('invoices', [
        'id' => $invoice->id,
        'status' => InvoiceStatusEnum::PAID->value,
    ]);
});

it('cannot mark invoice as paid with outstanding balance', function (): void {
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::PENDING,
        'total' => 100000,
        'paid' => 0,
    ]);

    // Partial payment only
    Payment::factory()->create([
        'amount' => 50000,
        'method' => PaymentMethodEnum::CASH,
        'related_type' => Invoice::class,
        'related_id' => $invoice->id,
    ]);

    $response = $this->post(route('invoices.mark-as-paid', $invoice));

    $response->assertRedirect();
    $response->assertSessionHasErrors(['message']);
});

it('may send invoice email', function (): void {
    Log::shouldReceive('info')
        ->once()
        ->with('Invoice email sent', Mockery::type('array'));

    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::PENDING,
    ]);

    $response = $this->post(route('invoices.send-email', $invoice), [
        'email' => 'customer@example.com',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('message', 'Invoice email sent successfully.');
});

it('cannot send email with invalid email address', function (): void {
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::PENDING,
    ]);

    $response = $this->post(route('invoices.send-email', $invoice), [
        'email' => 'invalid-email',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['message']);
});

it('cannot send cancelled invoice email', function (): void {
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::CANCELLED,
    ]);

    $response = $this->post(route('invoices.send-email', $invoice), [
        'email' => 'customer@example.com',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['message']);
});
