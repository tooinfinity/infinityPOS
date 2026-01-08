<?php

declare(strict_types=1);

use App\Models\InvoicePayment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

test('to array', function (): void {
    $invoicePayment = InvoicePayment::factory()->create()->refresh();

    expect(array_keys($invoicePayment->toArray()))
        ->toBe([
            'id',
            'invoice_id',
            'payment_date',
            'amount',
            'payment_method',
            'reference_number',
            'notes',
            'recorded_by',
            'created_at',
            'updated_at',
        ]);
});

test('invoice relationship returns belongs to', function (): void {
    $invoicePayment = new InvoicePayment();

    expect($invoicePayment->invoice())
        ->toBeInstanceOf(BelongsTo::class);
});

test('recorder relationship returns belongs to', function (): void {
    $invoicePayment = new InvoicePayment();

    expect($invoicePayment->recorder())
        ->toBeInstanceOf(BelongsTo::class);
});

test('casts returns correct array', function (): void {
    $invoicePayment = new InvoicePayment();

    expect($invoicePayment->casts())
        ->toBe([
            'id' => 'integer',
            'invoice_id' => 'integer',
            'payment_date' => 'date',
            'amount' => 'integer',
            'payment_method' => App\Enums\PaymentMethodEnum::class,
            'reference_number' => 'string',
            'notes' => 'string',
            'recorded_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $invoicePayment = InvoicePayment::factory()->create()->refresh();

    expect($invoicePayment->id)->toBeInt()
        ->and($invoicePayment->amount)->toBeInt()
        ->and($invoicePayment->payment_date)->toBeInstanceOf(DateTimeInterface::class)
        ->and($invoicePayment->created_at)->toBeInstanceOf(DateTimeInterface::class);
});

test('casts payment_method to PaymentMethodEnum', function (): void {
    $invoicePayment = InvoicePayment::factory()->create([
        'payment_method' => App\Enums\PaymentMethodEnum::CASH,
    ]);

    expect($invoicePayment->payment_method)->toBeInstanceOf(App\Enums\PaymentMethodEnum::class)
        ->and($invoicePayment->payment_method)->toBe(App\Enums\PaymentMethodEnum::CASH);
});

test('can set payment_method using enum value', function (): void {
    $invoicePayment = InvoicePayment::factory()->create([
        'payment_method' => 'card',
    ]);

    expect($invoicePayment->payment_method)->toBeInstanceOf(App\Enums\PaymentMethodEnum::class)
        ->and($invoicePayment->payment_method->value)->toBe('card');
});

test('can access enum methods on payment_method', function (): void {
    $invoicePayment = InvoicePayment::factory()->create([
        'payment_method' => App\Enums\PaymentMethodEnum::BANK_TRANSFER,
    ]);

    expect($invoicePayment->payment_method->label())->toBe('Bank Transfer')
        ->and($invoicePayment->payment_method->color())->toBeString()
        ->and($invoicePayment->payment_method->icon())->toBeString()
        ->and($invoicePayment->payment_method->isCash())->toBeFalse();
});
