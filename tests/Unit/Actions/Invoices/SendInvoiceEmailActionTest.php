<?php

declare(strict_types=1);

use App\Actions\Invoices\SendInvoiceEmail;
use App\Enums\InvoiceStatusEnum;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;

it('may send invoice email with valid email', function (): void {
    Log::shouldReceive('info')
        ->once()
        ->with('Invoice email sent', Mockery::type('array'));

    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::PENDING,
    ]);

    $action = resolve(SendInvoiceEmail::class);

    $result = $action->handle($invoice, 'customer@example.com');

    expect($result)->toBeTrue();
});

it('may send email for draft invoice', function (): void {
    Log::shouldReceive('info')
        ->once();

    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::DRAFT,
    ]);

    $action = resolve(SendInvoiceEmail::class);

    $result = $action->handle($invoice, 'test@example.com');

    expect($result)->toBeTrue();
});

it('may send email for paid invoice', function (): void {
    Log::shouldReceive('info')
        ->once();

    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::PAID,
    ]);

    $action = resolve(SendInvoiceEmail::class);

    $result = $action->handle($invoice, 'paid@example.com');

    expect($result)->toBeTrue();
});

it('cannot send cancelled invoice', function (): void {
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::CANCELLED,
    ]);

    $action = resolve(SendInvoiceEmail::class);

    $action->handle($invoice, 'customer@example.com');
})->throws(InvalidArgumentException::class, 'Cannot send a cancelled invoice');

it('throws exception for invalid email', function (): void {
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatusEnum::PENDING,
    ]);

    $action = resolve(SendInvoiceEmail::class);

    $action->handle($invoice, 'invalid-email');
})->throws(InvalidArgumentException::class, 'Invalid email address');
