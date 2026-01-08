<?php

declare(strict_types=1);

use App\DTOs\ReturnData;
use App\Enums\RefundMethodEnum;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Exceptions\CannotCreateData;

it('creates return DTO from array', function (): void {
    $data = ReturnData::from([
        'store_id' => 1,
        'sale_id' => 10,
        'invoice_id' => null,
        'return_date' => '2024-01-20 14:30:00',
        'total_amount' => 1500,
        'refund_method' => 'cash',
        'items' => [
            ['product_id' => 1, 'quantity' => 2, 'subtotal' => 1000],
            ['product_id' => 2, 'quantity' => 1, 'subtotal' => 500],
        ],
        'reason' => 'Defective product',
        'processed_by' => 5,
    ]);

    expect($data->storeId)->toBe(1)
        ->and($data->saleId)->toBe(10)
        ->and($data->invoiceId)->toBeNull()
        ->and($data->returnDate)->toBe('2024-01-20 14:30:00')
        ->and($data->totalAmount)->toBe(1500)
        ->and($data->refundMethod)->toBeInstanceOf(RefundMethodEnum::class)
        ->and($data->refundMethod)->toBe(RefundMethodEnum::CASH)
        ->and($data->items)->toBeArray()
        ->and($data->items)->toHaveCount(2)
        ->and($data->reason)->toBe('Defective product')
        ->and($data->processedBy)->toBe(5);
});

it('creates return DTO for invoice return', function (): void {
    $data = ReturnData::from([
        'store_id' => 1,
        'sale_id' => null,
        'invoice_id' => 20,
        'return_date' => '2024-01-20',
        'total_amount' => 3000,
        'refund_method' => 'store_credit',
        'items' => [],
    ]);

    expect($data->saleId)->toBeNull()
        ->and($data->invoiceId)->toBe(20)
        ->and($data->refundMethod)->toBe(RefundMethodEnum::STORE_CREDIT);
});

it('creates return DTO without optional fields', function (): void {
    $data = ReturnData::from([
        'store_id' => 1,
        'sale_id' => null,
        'invoice_id' => null,
        'return_date' => '2024-01-20',
        'total_amount' => 500,
        'refund_method' => 'store_credit',
    ]);

    expect($data->saleId)->toBeNull()
        ->and($data->invoiceId)->toBeNull()
        ->and($data->items)->toBeArray()
        ->and($data->items)->toBeEmpty()
        ->and($data->reason)->toBeNull()
        ->and($data->processedBy)->toBeNull();
});

it('calculates total from items', function (): void {
    $data = ReturnData::from([
        'store_id' => 1,
        'sale_id' => 5,
        'invoice_id' => null,
        'return_date' => '2024-01-20',
        'total_amount' => 2500,
        'refund_method' => 'cash',
        'items' => [
            ['product_id' => 1, 'quantity' => 5, 'subtotal' => 1000],
            ['product_id' => 2, 'quantity' => 3, 'subtotal' => 1500],
        ],
    ]);

    expect($data->calculateTotal())->toBe(2500);
});

it('calculates zero total with empty items', function (): void {
    $data = ReturnData::from([
        'store_id' => 1,
        'sale_id' => 5,
        'invoice_id' => null,
        'return_date' => '2024-01-20',
        'total_amount' => 0,
        'refund_method' => 'cash',
        'items' => [],
    ]);

    expect($data->calculateTotal())->toBe(0);
});

it('validates required fields', function (): void {
    ReturnData::from([
        'store_id' => 1,
        'sale_id' => null,
    ]);
})->throws(CannotCreateData::class);

it('validates total amount is non-negative', function (): void {
    ReturnData::validateAndCreate([
        'store_id' => 1,
        'sale_id' => null,
        'invoice_id' => null,
        'return_date' => '2024-01-20',
        'total_amount' => -500,
        'refund_method' => 'cash',
    ]);
})->throws(ValidationException::class);

it('handles snake_case mapping', function (): void {
    $data = ReturnData::validateAndCreate([
        'store_id' => 2,
        'sale_id' => 15,
        'invoice_id' => null,
        'return_date' => '2024-02-01',
        'total_amount' => 800,
        'refund_method' => 'card',
        'processed_by' => 10,
    ]);

    expect($data->storeId)->toBe(2)
        ->and($data->saleId)->toBe(15)
        ->and($data->invoiceId)->toBeNull()
        ->and($data->returnDate)->toBe('2024-02-01')
        ->and($data->totalAmount)->toBe(800)
        ->and($data->refundMethod)->toBe(RefundMethodEnum::CARD)
        ->and($data->processedBy)->toBe(10);
});

it('handles items with string subtotals', function (): void {
    $data = ReturnData::from([
        'store_id' => 1,
        'sale_id' => 5,
        'invoice_id' => null,
        'return_date' => '2024-01-20',
        'total_amount' => 1500,
        'refund_method' => 'cash',
        'items' => [
            ['product_id' => 1, 'quantity' => 2, 'subtotal' => '1000'],
            ['product_id' => 2, 'quantity' => 1, 'subtotal' => '500'],
        ],
    ]);

    expect($data->calculateTotal())->toBe(1500);
});

it('handles items without subtotal', function (): void {
    $data = ReturnData::from([
        'store_id' => 1,
        'sale_id' => 5,
        'invoice_id' => null,
        'return_date' => '2024-01-20',
        'total_amount' => 0,
        'refund_method' => 'cash',
        'items' => [
            ['product_id' => 1, 'quantity' => 2],
        ],
    ]);

    expect($data->calculateTotal())->toBe(0);
});

it('validates refund method with enum', function (): void {
    $data = ReturnData::from([
        'store_id' => 1,
        'sale_id' => 10,
        'invoice_id' => null,
        'return_date' => '2024-01-20',
        'total_amount' => 1000,
        'refund_method' => 'card',
    ]);

    expect($data->refundMethod)->toBeInstanceOf(RefundMethodEnum::class)
        ->and($data->refundMethod)->toBe(RefundMethodEnum::CARD);
});

it('accepts enum case values for refund method', function (): void {
    $data = ReturnData::from([
        'store_id' => 1,
        'sale_id' => 10,
        'invoice_id' => null,
        'return_date' => '2024-01-20',
        'total_amount' => 1000,
        'refund_method' => RefundMethodEnum::STORE_CREDIT->value,
    ]);

    expect($data->refundMethod)->toBe(RefundMethodEnum::STORE_CREDIT);
});

it('can use enum directly for refund method', function (): void {
    $data = ReturnData::from([
        'store_id' => 1,
        'sale_id' => 10,
        'invoice_id' => null,
        'return_date' => '2024-01-20',
        'total_amount' => 1000,
        'refund_method' => RefundMethodEnum::CASH,
    ]);

    expect($data->refundMethod)->toBe(RefundMethodEnum::CASH);
});

it('rejects invalid refund method', function (): void {
    ReturnData::validateAndCreate([
        'store_id' => 1,
        'sale_id' => 10,
        'invoice_id' => null,
        'return_date' => '2024-01-20',
        'total_amount' => 1000,
        'refund_method' => 'invalid-method',
    ]);
})->throws(ValidationException::class);
