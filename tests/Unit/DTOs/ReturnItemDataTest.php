<?php

declare(strict_types=1);

use App\DTOs\ReturnItemData;
use Illuminate\Validation\ValidationException;

it('creates return item DTO from array', function (): void {
    $data = ReturnItemData::from([
        'return_id' => 1,
        'sale_item_id' => 5,
        'invoice_item_id' => null,
        'product_id' => 10,
        'quantity' => 2,
        'unit_price' => 150,
        'unit_cost' => 90,
    ]);

    expect($data->returnId)->toBe(1)
        ->and($data->saleItemId)->toBe(5)
        ->and($data->invoiceItemId)->toBeNull()
        ->and($data->productId)->toBe(10)
        ->and($data->quantity)->toBe(2)
        ->and($data->unitPrice)->toBe(150)
        ->and($data->unitCost)->toBe(90);
});

it('calculates subtotal correctly', function (): void {
    $data = ReturnItemData::from([
        'return_id' => 1,
        'product_id' => 10,
        'quantity' => 3,
        'unit_price' => 200,
        'unit_cost' => 120,
    ]);

    expect($data->subtotal())->toBe(600);
});

it('calculates refund amount', function (): void {
    $data = ReturnItemData::from([
        'return_id' => 1,
        'product_id' => 10,
        'quantity' => 2,
        'unit_price' => 500,
        'unit_cost' => 300,
    ]);

    expect($data->refundAmount())->toBe(1000);
});

it('validates quantity is at least 1', function (): void {
    ReturnItemData::validateAndCreate([
        'return_id' => 1,
        'product_id' => 10,
        'quantity' => 0,
        'unit_price' => 100,
        'unit_cost' => 60,
    ]);
})->throws(ValidationException::class);

it('creates return item with invoice item id', function (): void {
    $data = ReturnItemData::validateAndCreate([
        'return_id' => 1,
        'invoice_item_id' => 20,
        'product_id' => 10,
        'quantity' => 1,
        'unit_price' => 100,
        'unit_cost' => 60,
    ]);

    expect($data->invoiceItemId)->toBe(20)
        ->and($data->saleItemId)->toBeNull();
});

it('gets subtotal using getSubtotal method', function (): void {
    $data = ReturnItemData::from([
        'product_id' => 10,
        'quantity' => 4,
        'unit_price' => 250,
    ]);

    expect($data->getSubtotal())->toBe(1000);
});

it('calculates subtotal using calculateSubtotal method', function (): void {
    $data = ReturnItemData::from([
        'product_id' => 10,
        'quantity' => 5,
        'unit_price' => 100,
    ]);

    expect($data->calculateSubtotal())->toBe(500);
});
