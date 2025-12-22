<?php

declare(strict_types=1);

use App\Actions\Invoices\CancelInvoice;
use App\Enums\InvoiceStatusEnum;
use App\Models\Invoice;
use App\Models\User;

it('may cancel a pending invoice', function (): void {
    $user = User::factory()->create();
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::PENDING,
    ]);

    $action = resolve(CancelInvoice::class);

    $cancelledInvoice = $action->handle($invoice, $user->id);

    expect($cancelledInvoice->status)->toBe(InvoiceStatusEnum::CANCELLED)
        ->and($cancelledInvoice->updated_by)->toBe($user->id);
});

it('may cancel a draft invoice', function (): void {
    $user = User::factory()->create();
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::DRAFT,
    ]);

    $action = resolve(CancelInvoice::class);

    $cancelledInvoice = $action->handle($invoice, $user->id);

    expect($cancelledInvoice->status)->toBe(InvoiceStatusEnum::CANCELLED);
});

it('returns already cancelled invoice', function (): void {
    $user = User::factory()->create();
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::CANCELLED,
    ]);

    $action = resolve(CancelInvoice::class);

    $cancelledInvoice = $action->handle($invoice, $user->id);

    expect($cancelledInvoice->status)->toBe(InvoiceStatusEnum::CANCELLED);
});

it('cannot cancel a paid invoice', function (): void {
    $user = User::factory()->create();
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::PAID,
    ]);

    $action = resolve(CancelInvoice::class);

    $action->handle($invoice, $user->id);
})->throws(InvalidArgumentException::class, 'Cannot cancel a paid invoice');
