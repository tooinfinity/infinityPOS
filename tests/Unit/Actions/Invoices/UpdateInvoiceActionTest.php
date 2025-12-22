<?php

declare(strict_types=1);

use App\Actions\Invoices\UpdateInvoice;
use App\Data\Invoices\UpdateInvoiceData;
use App\Enums\InvoiceStatusEnum;
use App\Models\Invoice;
use App\Models\User;

it('may update invoice reference', function (): void {
    $user = User::factory()->create();
    $invoice = Invoice::factory()->create([
        'reference' => 'INV-001',
        'status' => InvoiceStatusEnum::PENDING,
    ]);

    $action = resolve(UpdateInvoice::class);

    $data = UpdateInvoiceData::from([
        'reference' => 'INV-001-UPDATED',
        'due_at' => null,
        'notes' => null,
        'updated_by' => $user->id,
    ]);

    $updatedInvoice = $action->handle($invoice, $data);

    expect($updatedInvoice->reference)->toBe('INV-001-UPDATED');
});

it('may update invoice due date', function (): void {
    $user = User::factory()->create();
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::PENDING,
        'due_at' => now()->addDays(30),
    ]);

    $action = resolve(UpdateInvoice::class);

    $newDueDate = now()->addDays(60)->toDateString();
    $data = UpdateInvoiceData::from([
        'reference' => null,
        'due_at' => $newDueDate,
        'notes' => null,
        'updated_by' => $user->id,
    ]);

    $updatedInvoice = $action->handle($invoice, $data);

    expect($updatedInvoice->due_at->toDateString())->toBe($newDueDate);
});

it('may update invoice notes', function (): void {
    $user = User::factory()->create();
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::PENDING,
        'notes' => 'Old notes',
    ]);

    $action = resolve(UpdateInvoice::class);

    $data = UpdateInvoiceData::from([
        'reference' => null,
        'due_at' => null,
        'notes' => 'Updated notes',
        'updated_by' => $user->id,
    ]);

    $updatedInvoice = $action->handle($invoice, $data);

    expect($updatedInvoice->notes)->toBe('Updated notes');
});

it('cannot update a paid invoice', function (): void {
    $user = User::factory()->create();
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::PAID,
    ]);

    $action = resolve(UpdateInvoice::class);

    $data = UpdateInvoiceData::from([
        'reference' => 'INV-NEW',
        'due_at' => null,
        'notes' => null,
        'updated_by' => $user->id,
    ]);

    $action->handle($invoice, $data);
})->throws(InvalidArgumentException::class, 'Cannot update a paid invoice');

it('cannot update a cancelled invoice', function (): void {
    $user = User::factory()->create();
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::CANCELLED,
    ]);

    $action = resolve(UpdateInvoice::class);

    $data = UpdateInvoiceData::from([
        'reference' => 'INV-NEW',
        'due_at' => null,
        'notes' => null,
        'updated_by' => $user->id,
    ]);

    $action->handle($invoice, $data);
})->throws(InvalidArgumentException::class, 'Cannot update a cancelled invoice');
