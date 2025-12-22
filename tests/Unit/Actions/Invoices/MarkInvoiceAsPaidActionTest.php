<?php

declare(strict_types=1);

use App\Actions\Invoices\MarkInvoiceAsPaid;
use App\Enums\InvoiceStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;

it('may mark invoice as paid when fully paid', function (): void {
    $user = User::factory()->create();
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

    $action = resolve(MarkInvoiceAsPaid::class);

    $paidInvoice = $action->handle($invoice, $user->id);

    expect($paidInvoice->status)->toBe(InvoiceStatusEnum::PAID)
        ->and($paidInvoice->paid_at)->not->toBeNull()
        ->and($paidInvoice->updated_by)->toBe($user->id);
});

it('returns already paid invoice', function (): void {
    $user = User::factory()->create();
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::PAID,
        'paid_at' => now(),
    ]);

    $action = resolve(MarkInvoiceAsPaid::class);

    $paidInvoice = $action->handle($invoice, $user->id);

    expect($paidInvoice->status)->toBe(InvoiceStatusEnum::PAID);
});

it('cannot mark invoice as paid with outstanding balance', function (): void {
    $user = User::factory()->create();
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::PENDING,
        'total' => 100000,
        'paid' => 0,
    ]);

    // Partial payment
    Payment::factory()->create([
        'amount' => 50000,
        'method' => PaymentMethodEnum::CASH,
        'related_type' => Invoice::class,
        'related_id' => $invoice->id,
    ]);

    $action = resolve(MarkInvoiceAsPaid::class);

    $action->handle($invoice, $user->id);
})->throws(InvalidArgumentException::class, 'Invoice still has outstanding balance');

it('cannot mark cancelled invoice as paid', function (): void {
    $user = User::factory()->create();
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::CANCELLED,
    ]);

    $action = resolve(MarkInvoiceAsPaid::class);

    $action->handle($invoice, $user->id);
})->throws(InvalidArgumentException::class, 'Cannot mark a cancelled invoice as paid');
