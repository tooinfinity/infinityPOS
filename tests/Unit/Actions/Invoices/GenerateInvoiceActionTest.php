<?php

declare(strict_types=1);

use App\Actions\Invoices\GenerateInvoice;
use App\Data\Invoices\GenerateInvoiceData;
use App\Enums\InvoiceStatusEnum;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Sale;
use App\Models\User;

it('may generate an invoice from a sale', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->create(['created_by' => $user->id]);
    $sale = Sale::factory()->create([
        'client_id' => $client->id,
        'subtotal' => 100000,
        'discount' => 5000,
        'tax' => 9500,
        'total' => 104500,
        'paid' => 0,
        'created_by' => $user->id,
    ]);

    $action = resolve(GenerateInvoice::class);

    $data = GenerateInvoiceData::from([
        'reference' => 'INV-001',
        'sale_id' => $sale->id,
        'client_id' => $client->id,
        'issued_at' => now()->toDateString(),
        'due_at' => now()->addDays(30)->toDateString(),
        'notes' => 'Test invoice',
        'created_by' => $user->id,
    ]);

    $invoice = $action->handle($data);

    expect($invoice)->toBeInstanceOf(Invoice::class)
        ->and($invoice->reference)->toBe('INV-001')
        ->and($invoice->sale_id)->toBe($sale->id)
        ->and($invoice->client_id)->toBe($client->id)
        ->and($invoice->status)->toBe(InvoiceStatusEnum::PENDING)
        ->and($invoice->subtotal)->toBe(100000)
        ->and($invoice->total)->toBe(104500)
        ->and($invoice->paid)->toBe(0)
        ->and($invoice->notes)->toBe('Test invoice');
});

it('uses sale client_id when not provided', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->create(['created_by' => $user->id]);
    $sale = Sale::factory()->create([
        'client_id' => $client->id,
        'created_by' => $user->id,
    ]);

    $action = resolve(GenerateInvoice::class);

    $data = GenerateInvoiceData::from([
        'reference' => 'INV-002',
        'sale_id' => $sale->id,
        'client_id' => null,
        'issued_at' => now()->toDateString(),
        'due_at' => null,
        'notes' => null,
        'created_by' => $user->id,
    ]);

    $invoice = $action->handle($data);

    expect($invoice->client_id)->toBe($client->id);
});

it('throws exception when invoice already exists for sale', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->create(['created_by' => $user->id]);
    Invoice::factory()->create(['sale_id' => $sale->id]);

    $action = resolve(GenerateInvoice::class);

    $data = GenerateInvoiceData::from([
        'reference' => 'INV-003',
        'sale_id' => $sale->id,
        'client_id' => null,
        'issued_at' => now()->toDateString(),
        'due_at' => null,
        'notes' => null,
        'created_by' => $user->id,
    ]);

    $action->handle($data);
})->throws(InvalidArgumentException::class, 'Invoice already exists for this sale');

it('generates invoice with PENDING status', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->create(['created_by' => $user->id]);

    $action = resolve(GenerateInvoice::class);

    $data = GenerateInvoiceData::from([
        'reference' => 'INV-004',
        'sale_id' => $sale->id,
        'client_id' => null,
        'issued_at' => now()->toDateString(),
        'due_at' => now()->addDays(15)->toDateString(),
        'notes' => null,
        'created_by' => $user->id,
    ]);

    $invoice = $action->handle($data);

    expect($invoice->status)->toBe(InvoiceStatusEnum::PENDING)
        ->and($invoice->paid_at)->toBeNull();
});
