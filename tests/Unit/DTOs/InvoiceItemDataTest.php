<?php

declare(strict_types=1);

use App\DTOs\InvoiceItemData;
use Illuminate\Validation\ValidationException;

it('creates invoice item DTO from array', function (): void {
    $data = InvoiceItemData::from([
        'invoice_id' => 1,
        'product_id' => 10,
        'quantity' => 5,
        'unit_price' => 100,
        'unit_cost' => 60,
    ]);

    expect($data->invoiceId)->toBe(1)
        ->and($data->productId)->toBe(10)
        ->and($data->quantity)->toBe(5)
        ->and($data->unitPrice)->toBe(100)
        ->and($data->unitCost)->toBe(60);
});

it('calculates subtotal correctly', function (): void {
    $data = InvoiceItemData::from([
        'invoice_id' => 1,
        'product_id' => 10,
        'quantity' => 3,
        'unit_price' => 250,
        'unit_cost' => 150,
    ]);

    expect($data->subtotal())->toBe(750);
});

it('calculates profit correctly', function (): void {
    $data = InvoiceItemData::from([
        'invoice_id' => 1,
        'product_id' => 10,
        'quantity' => 4,
        'unit_price' => 200,
        'unit_cost' => 120,
    ]);

    expect($data->profit())->toBe(320); // (200 - 120) * 4
});

it('validates quantity is at least 1', function (): void {
    InvoiceItemData::validateAndCreate([
        'invoice_id' => 1,
        'product_id' => 10,
        'quantity' => 0,
        'unit_price' => 100,
        'unit_cost' => 60,
    ]);
})->throws(ValidationException::class);

it('validates required fields', function (): void {
    InvoiceItemData::validateAndCreate([
        'invoice_id' => 1,
        'product_id' => 10,
    ]);
})->throws(ValidationException::class);
