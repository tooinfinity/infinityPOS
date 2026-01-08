<?php

declare(strict_types=1);

use App\DTOs\SaleItemData;
use Illuminate\Validation\ValidationException;

it('creates sale item DTO from array', function (): void {
    $data = SaleItemData::from([
        'product_id' => 1,
        'quantity' => 5,
        'unit_price' => 100,
        'unit_cost' => 60,
    ]);

    expect($data->productId)->toBe(1)
        ->and($data->quantity)->toBe(5)
        ->and($data->unitPrice)->toBe(100)
        ->and($data->unitCost)->toBe(60);
});

it('creates sale item DTO without unit cost', function (): void {
    $data = SaleItemData::from([
        'product_id' => 1,
        'quantity' => 3,
        'unit_price' => 200,
    ]);

    expect($data->unitCost)->toBeNull();
});

it('calculates subtotal correctly', function (): void {
    $data = SaleItemData::from([
        'product_id' => 1,
        'quantity' => 4,
        'unit_price' => 250,
    ]);

    expect($data->subtotal())->toBe(1000);
});

it('calculates profit when unit cost is provided', function (): void {
    $data = SaleItemData::from([
        'product_id' => 1,
        'quantity' => 5,
        'unit_price' => 150,
        'unit_cost' => 100,
    ]);

    expect($data->profit())->toBe(250); // (150 - 100) * 5
});

it('returns zero profit when unit cost is null', function (): void {
    $data = SaleItemData::from([
        'product_id' => 1,
        'quantity' => 5,
        'unit_price' => 150,
    ]);

    expect($data->profit())->toBe(0);
});

it('validates quantity is at least 1', function (): void {
    SaleItemData::validateAndCreate([
        'product_id' => 1,
        'quantity' => 0,
        'unit_price' => 100,
    ]);
})->throws(ValidationException::class);

it('validates required fields', function (): void {
    SaleItemData::validateAndCreate([
        'product_id' => 1,
    ]);
})->throws(ValidationException::class);

it('handles snake_case mapping', function (): void {
    $data = SaleItemData::from([
        'product_id' => 10,
        'quantity' => 2,
        'unit_price' => 500,
        'unit_cost' => 300,
    ]);

    expect($data->productId)->toBe(10)
        ->and($data->unitPrice)->toBe(500)
        ->and($data->unitCost)->toBe(300);
});
