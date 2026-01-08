<?php

declare(strict_types=1);

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('to array', function (): void {
    $invoice = Invoice::factory()->create()->refresh();

    expect(array_keys($invoice->toArray()))
        ->toBe([
            'id',
            'store_id',
            'customer_id',
            'invoice_number',
            'invoice_date',
            'due_date',
            'subtotal',
            'discount_amount',
            'total_amount',
            'paid_amount',
            'payment_status',
            'notes',
            'terms',
            'created_by',
            'created_at',
            'updated_at',
        ]);
});

test('store relationship returns belongs to', function (): void {
    $invoice = new Invoice();

    expect($invoice->store())
        ->toBeInstanceOf(BelongsTo::class);
});

test('customer relationship returns belongs to', function (): void {
    $invoice = new Invoice();

    expect($invoice->customer())
        ->toBeInstanceOf(BelongsTo::class);
});

test('creator relationship returns belongs to', function (): void {
    $invoice = new Invoice();

    expect($invoice->creator())
        ->toBeInstanceOf(BelongsTo::class);
});

test('items relationship returns has many', function (): void {
    $invoice = new Invoice();

    expect($invoice->items())
        ->toBeInstanceOf(HasMany::class);
});

test('payments relationship returns has many', function (): void {
    $invoice = new Invoice();

    expect($invoice->payments())
        ->toBeInstanceOf(HasMany::class);
});

test('returns relationship returns has many', function (): void {
    $invoice = new Invoice();

    expect($invoice->returns())
        ->toBeInstanceOf(HasMany::class);
});

test('get outstanding balance returns correct amount', function (): void {
    $invoice = Invoice::factory()->make([
        'total_amount' => 10000,
        'paid_amount' => 6000,
    ]);

    expect($invoice->getOutstandingBalance())->toBe(4000);
});

test('get outstanding balance returns zero when fully paid', function (): void {
    $invoice = Invoice::factory()->make([
        'total_amount' => 10000,
        'paid_amount' => 10000,
    ]);

    expect($invoice->getOutstandingBalance())->toBe(0);
});

test('is fully paid returns true when payment status is paid', function (): void {
    $invoice = Invoice::factory()->make(['payment_status' => 'paid']);

    expect($invoice->isFullyPaid())->toBeTrue();
});

test('is fully paid returns false when payment status is not paid', function (): void {
    $invoice = Invoice::factory()->make(['payment_status' => 'unpaid']);

    expect($invoice->isFullyPaid())->toBeFalse();
});

test('is overdue returns true when payment status is overdue', function (): void {
    $invoice = Invoice::factory()->make(['payment_status' => 'overdue']);

    expect($invoice->isOverdue())->toBeTrue();
});

test('is overdue returns true when due date is past and not fully paid', function (): void {
    $invoice = Invoice::factory()->make([
        'payment_status' => 'unpaid',
        'due_date' => now()->subDays(5),
        'total_amount' => 10000,
        'paid_amount' => 5000,
    ]);

    expect($invoice->isOverdue())->toBeTrue();
});

test('is overdue returns false when due date is past but fully paid', function (): void {
    $invoice = Invoice::factory()->make([
        'payment_status' => 'paid',
        'due_date' => now()->subDays(5),
    ]);

    expect($invoice->isOverdue())->toBeFalse();
});

test('is overdue returns false when due date is future', function (): void {
    $invoice = Invoice::factory()->make([
        'payment_status' => 'unpaid',
        'due_date' => now()->addDays(5),
    ]);

    expect($invoice->isOverdue())->toBeFalse();
});

test('is overdue returns false when no due date', function (): void {
    $invoice = Invoice::factory()->make([
        'payment_status' => 'unpaid',
        'due_date' => null,
    ]);

    expect($invoice->isOverdue())->toBeFalse();
});

test('casts returns correct array', function (): void {
    $invoice = new Invoice();

    expect($invoice->casts())
        ->toBe([
            'id' => 'integer',
            'store_id' => 'integer',
            'customer_id' => 'integer',
            'invoice_number' => 'string',
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'integer',
            'discount_amount' => 'integer',
            'total_amount' => 'integer',
            'paid_amount' => 'integer',
            'payment_status' => App\Enums\InvoicePaymentStatusEnum::class,
            'notes' => 'string',
            'terms' => 'string',
            'created_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $invoice = Invoice::factory()->create()->refresh();

    expect($invoice->id)->toBeInt()
        ->and($invoice->total_amount)->toBeInt()
        ->and($invoice->invoice_date)->toBeInstanceOf(DateTimeInterface::class)
        ->and($invoice->created_at)->toBeInstanceOf(DateTimeInterface::class);
});

test('casts payment_status to InvoicePaymentStatusEnum', function (): void {
    $invoice = Invoice::factory()->create([
        'payment_status' => App\Enums\InvoicePaymentStatusEnum::PAID,
    ]);

    expect($invoice->payment_status)->toBeInstanceOf(App\Enums\InvoicePaymentStatusEnum::class)
        ->and($invoice->payment_status)->toBe(App\Enums\InvoicePaymentStatusEnum::PAID);
});

test('can set payment_status using enum value', function (): void {
    $invoice = Invoice::factory()->create([
        'payment_status' => 'partial',
    ]);

    expect($invoice->payment_status)->toBeInstanceOf(App\Enums\InvoicePaymentStatusEnum::class)
        ->and($invoice->payment_status->value)->toBe('partial');
});

test('can access enum methods on payment_status', function (): void {
    $invoice = Invoice::factory()->create([
        'payment_status' => App\Enums\InvoicePaymentStatusEnum::OVERDUE,
    ]);

    expect($invoice->payment_status->label())->toBe('Overdue')
        ->and($invoice->payment_status->color())->toBeString()
        ->and($invoice->payment_status->icon())->toBeString()
        ->and($invoice->payment_status->requiresAction())->toBeTrue();
});
