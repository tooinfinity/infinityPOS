<?php

declare(strict_types=1);

use App\DTOs\PurchaseItemData;
use Illuminate\Validation\ValidationException;

it('creates purchase item DTO from array', function (): void {
    $data = PurchaseItemData::from([
        'product_id' => 1,
        'quantity' => 10,
        'unit_cost' => 50,
        'expiry_date' => '2025-12-31',
        'batch_number' => 'BATCH-001',
    ]);

    expect($data->productId)->toBe(1)
        ->and($data->quantity)->toBe(10)
        ->and($data->unitCost)->toBe(50)
        ->and($data->expiryDate)->toBe('2025-12-31')
        ->and($data->batchNumber)->toBe('BATCH-001');
});

it('creates purchase item without optional fields', function (): void {
    $data = PurchaseItemData::from([
        'product_id' => 1,
        'quantity' => 5,
        'unit_cost' => 75,
    ]);

    expect($data->expiryDate)->toBeNull()
        ->and($data->batchNumber)->toBeNull();
});

it('calculates subtotal correctly', function (): void {
    $data = PurchaseItemData::from([
        'product_id' => 1,
        'quantity' => 8,
        'unit_cost' => 125,
    ]);

    expect($data->subtotal())->toBe(1000);
});

it('validates quantity is at least 1', function (): void {
    PurchaseItemData::validateAndCreate([
        'product_id' => 1,
        'quantity' => 0,
        'unit_cost' => 100,
    ]);
})->throws(ValidationException::class);

it('validates required fields', function (): void {
    PurchaseItemData::validateAndCreate([
        'product_id' => 1,
        'quantity' => 5,
    ]);
})->throws(ValidationException::class);

it('validates date format for expiry date', function (): void {
    PurchaseItemData::validateAndCreate([
        'product_id' => 1,
        'quantity' => 5,
        'unit_cost' => 100,
        'expiry_date' => 'invalid-date',
    ]);
})->throws(ValidationException::class);

it('handles snake_case mapping', function (): void {
    $data = PurchaseItemData::validateAndCreate([
        'product_id' => 5,
        'quantity' => 3,
        'unit_cost' => 200,
        'expiry_date' => '2026-01-01',
        'batch_number' => 'BTN-123',
    ]);

    expect($data->productId)->toBe(5)
        ->and($data->unitCost)->toBe(200)
        ->and($data->expiryDate)->toBe('2026-01-01')
        ->and($data->batchNumber)->toBe('BTN-123');
});
