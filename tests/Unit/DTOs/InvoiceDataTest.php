<?php

declare(strict_types=1);

use App\DTOs\InvoiceData;
use App\Enums\InvoicePaymentStatusEnum;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Exceptions\CannotCreateData;

it('creates invoice DTO from array', function (): void {
    $data = InvoiceData::from([
        'store_id' => 1,
        'customer_id' => 5,
        'invoice_date' => '2024-01-15',
        'due_date' => '2024-02-15',
        'subtotal' => 10000,
        'discount_amount' => 500,
        'total_amount' => 9500,
        'items' => [
            ['product_id' => 1, 'quantity' => 10, 'unit_price' => 1000],
        ],
        'notes' => 'Net 30 payment terms',
        'terms' => 'Payment due within 30 days',
    ]);

    expect($data->storeId)->toBe(1)
        ->and($data->customerId)->toBe(5)
        ->and($data->invoiceDate)->toBe('2024-01-15')
        ->and($data->dueDate)->toBe('2024-02-15')
        ->and($data->subtotal)->toBe(10000)
        ->and($data->discountAmount)->toBe(500)
        ->and($data->totalAmount)->toBe(9500)
        ->and($data->paymentStatus)->toBeInstanceOf(InvoicePaymentStatusEnum::class)
        ->and($data->items)->toBeArray()
        ->and($data->items)->toHaveCount(1)
        ->and($data->notes)->toBe('Net 30 payment terms')
        ->and($data->terms)->toBe('Payment due within 30 days');
});

it('creates invoice DTO without optional fields', function (): void {
    $data = InvoiceData::from([
        'store_id' => 1,
        'customer_id' => 5,
        'invoice_date' => '2024-01-15',
        'due_date' => null,
        'subtotal' => 5000,
        'discount_amount' => 0,
        'total_amount' => 5000,
    ]);

    expect($data->dueDate)->toBeNull()
        ->and($data->discountAmount)->toBe(0)
        ->and($data->items)->toBeArray()
        ->and($data->items)->toBeEmpty()
        ->and($data->notes)->toBeNull()
        ->and($data->terms)->toBeNull();
});

it('calculates remaining balance with no payment', function (): void {
    $data = InvoiceData::from([
        'store_id' => 1,
        'customer_id' => 5,
        'invoice_date' => '2024-01-15',
        'due_date' => null,
        'subtotal' => 10000,
        'discount_amount' => 0,
        'total_amount' => 10000,
    ]);

    expect($data->remainingBalance())->toBe(10000);
});

it('calculates remaining balance with partial payment', function (): void {
    $data = InvoiceData::from([
        'store_id' => 1,
        'customer_id' => 5,
        'invoice_date' => '2024-01-15',
        'due_date' => null,
        'subtotal' => 10000,
        'discount_amount' => 0,
        'total_amount' => 10000,
    ]);

    expect($data->remainingBalance(6000))->toBe(4000);
});

it('calculates zero balance when fully paid', function (): void {
    $data = InvoiceData::from([
        'store_id' => 1,
        'customer_id' => 5,
        'invoice_date' => '2024-01-15',
        'due_date' => null,
        'subtotal' => 5000,
        'discount_amount' => 0,
        'total_amount' => 5000,
    ]);

    expect($data->remainingBalance(5000))->toBe(0);
});

it('calculates zero balance when overpaid', function (): void {
    $data = InvoiceData::from([
        'store_id' => 1,
        'customer_id' => 5,
        'invoice_date' => '2024-01-15',
        'due_date' => null,
        'subtotal' => 5000,
        'discount_amount' => 0,
        'total_amount' => 5000,
    ]);

    expect($data->remainingBalance(7000))->toBe(0);
});

it('validates required fields', function (): void {
    InvoiceData::from([
        'store_id' => 1,
        'customer_id' => 5,
    ]);
})->throws(CannotCreateData::class);

it('validates date format', function (): void {
    InvoiceData::validateAndCreate([
        'store_id' => 1,
        'customer_id' => 5,
        'invoice_date' => 'invalid-date',
        'due_date' => null,
        'subtotal' => 1000,
        'discount_amount' => 0,
        'total_amount' => 1000,
    ]);
})->throws(ValidationException::class);

it('handles multiple invoice items', function (): void {
    $data = InvoiceData::validateAndCreate([
        'store_id' => 1,
        'customer_id' => 5,
        'invoice_date' => '2024-01-15',
        'due_date' => '2024-02-15',
        'subtotal' => 15000,
        'discount_amount' => 1000,
        'total_amount' => 14000,
        'items' => [
            ['product_id' => 1, 'quantity' => 10, 'unit_price' => 500],
            ['product_id' => 2, 'quantity' => 5, 'unit_price' => 2000],
        ],
    ]);

    expect($data->items)->toHaveCount(2);
});

it('validates payment status with enum', function (): void {
    $data = InvoiceData::from([
        'store_id' => 1,
        'customer_id' => 5,
        'invoice_date' => '2024-01-15',
        'due_date' => '2024-02-15',
        'subtotal' => 5000,
        'total_amount' => 5000,
        'payment_status' => 'paid',
    ]);

    expect($data->paymentStatus)->toBeInstanceOf(InvoicePaymentStatusEnum::class)
        ->and($data->paymentStatus)->toBe(InvoicePaymentStatusEnum::PAID);
});

it('accepts enum case values for payment status', function (): void {
    $data = InvoiceData::from([
        'store_id' => 1,
        'customer_id' => 5,
        'invoice_date' => '2024-01-15',
        'due_date' => '2024-02-15',
        'subtotal' => 5000,
        'total_amount' => 5000,
        'payment_status' => InvoicePaymentStatusEnum::PARTIAL->value,
    ]);

    expect($data->paymentStatus)->toBe(InvoicePaymentStatusEnum::PARTIAL);
});

it('can use enum directly for payment status', function (): void {
    $data = InvoiceData::from([
        'store_id' => 1,
        'customer_id' => 5,
        'invoice_date' => '2024-01-15',
        'due_date' => '2024-02-15',
        'subtotal' => 5000,
        'total_amount' => 5000,
        'payment_status' => InvoicePaymentStatusEnum::OVERDUE,
    ]);

    expect($data->paymentStatus)->toBe(InvoicePaymentStatusEnum::OVERDUE);
});

it('rejects invalid payment status', function (): void {
    InvoiceData::validateAndCreate([
        'store_id' => 1,
        'customer_id' => 5,
        'invoice_date' => '2024-01-15',
        'due_date' => '2024-02-15',
        'subtotal' => 5000,
        'total_amount' => 5000,
        'payment_status' => 'invalid-status',
    ]);
})->throws(ValidationException::class);

it('uses default payment status', function (): void {
    $data = InvoiceData::from([
        'store_id' => 1,
        'customer_id' => 5,
        'invoice_date' => '2024-01-15',
        'due_date' => '2024-02-15',
        'subtotal' => 5000,
        'total_amount' => 5000,
    ]);

    expect($data->paymentStatus)->toBe(InvoicePaymentStatusEnum::UNPAID);
});

it('handles snake_case mapping', function (): void {
    $data = InvoiceData::from([
        'store_id' => 2,
        'customer_id' => 10,
        'invoice_date' => '2024-03-01',
        'due_date' => '2024-04-01',
        'subtotal' => 8000,
        'discount_amount' => 200,
        'total_amount' => 7800,
        'payment_status' => 'unpaid',
    ]);

    expect($data->storeId)->toBe(2)
        ->and($data->customerId)->toBe(10)
        ->and($data->invoiceDate)->toBe('2024-03-01')
        ->and($data->dueDate)->toBe('2024-04-01')
        ->and($data->discountAmount)->toBe(200)
        ->and($data->totalAmount)->toBe(7800)
        ->and($data->paymentStatus)->toBe(InvoicePaymentStatusEnum::UNPAID);
});
