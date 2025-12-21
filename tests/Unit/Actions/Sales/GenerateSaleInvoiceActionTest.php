<?php

declare(strict_types=1);

use App\Actions\Sales\GenerateSaleInvoice;
use App\Enums\InvoiceStatusEnum;
use App\Models\Invoice;
use App\Models\Sale;
use App\Models\User;

it('may generate a sale invoice', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->create([
        'subtotal' => 45000,
        'discount' => 500,
        'tax' => 5500,
        'total' => 50000,
        'paid' => 0,
        'created_by' => $user->id,
    ]);
    $action = resolve(GenerateSaleInvoice::class);

    $issuedAt = now();
    $dueAt = now()->addDays(30);

    $invoice = $action->handle(
        sale: $sale,
        reference: 'INV-001',
        issuedAt: $issuedAt,
        dueAt: $dueAt,
        notes: 'Payment due in 30 days',
        userId: $user->id
    );

    expect($invoice)->toBeInstanceOf(Invoice::class)
        ->and($invoice->reference)->toBe('INV-001')
        ->and($invoice->sale_id)->toBe($sale->id)
        ->and($invoice->client_id)->toBe($sale->client_id)
        ->and($invoice->subtotal)->toBe(45000)
        ->and($invoice->discount)->toBe(500)
        ->and($invoice->tax)->toBe(5500)
        ->and($invoice->total)->toBe(50000)
        ->and($invoice->paid)->toBe(0)
        ->and($invoice->status)->toBe(InvoiceStatusEnum::PENDING)
        ->and($invoice->notes)->toBe('Payment due in 30 days')
        ->and($invoice->created_by)->toBe($user->id);
});

it('cannot generate invoice if one already exists', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->create(['created_by' => $user->id]);
    Invoice::factory()->create(['sale_id' => $sale->id]);

    $action = resolve(GenerateSaleInvoice::class);

    $action->handle(
        sale: $sale,
        reference: 'INV-002',
        issuedAt: now(),
        userId: $user->id
    );
})->throws(InvalidArgumentException::class, 'Invoice already exists for this sale.');
