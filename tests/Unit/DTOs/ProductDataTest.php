<?php

declare(strict_types=1);

use App\DTOs\ProductData;
use App\Enums\ProductUnitEnum;
use Illuminate\Validation\ValidationException;

it('creates product DTO from array', function (): void {
    $data = ProductData::from([
        'category_id' => 1,
        'name' => 'Test Product',
        'sku' => 'TEST-001',
        'barcode' => '123456789',
        'description' => 'A test product',
        'unit' => 'piece',
        'selling_price' => 1000,
        'alert_quantity' => 10,
        'image' => 'product.jpg',
        'is_active' => true,
    ]);

    expect($data->categoryId)->toBe(1)
        ->and($data->name)->toBe('Test Product')
        ->and($data->sku)->toBe('TEST-001')
        ->and($data->barcode)->toBe('123456789')
        ->and($data->description)->toBe('A test product')
        ->and($data->unit)->toBeInstanceOf(ProductUnitEnum::class)
        ->and($data->unit)->toBe(ProductUnitEnum::PIECE)
        ->and($data->sellingPrice)->toBe(1000)
        ->and($data->alertQuantity)->toBe(10)
        ->and($data->image)->toBe('product.jpg')
        ->and($data->isActive)->toBeTrue();
});

it('creates product DTO with minimal data', function (): void {
    $data = ProductData::from([
        'name' => 'Simple Product',
        'sku' => 'SIMPLE-001',
        'unit' => 'piece',
        'selling_price' => 500,
        'alert_quantity' => 5,
    ]);

    expect($data->name)->toBe('Simple Product')
        ->and($data->unit)->toBe(ProductUnitEnum::PIECE)
        ->and($data->categoryId)->toBeNull()
        ->and($data->barcode)->toBeNull()
        ->and($data->description)->toBeNull()
        ->and($data->image)->toBeNull()
        ->and($data->isActive)->toBeTrue();
});

it('validates required name field', function (): void {
    ProductData::validateAndCreate([
        'name' => '', // Empty string should fail Required validation
        'sku' => 'TEST',
        'unit' => 'piece',
        'selling_price' => 100,
        'alert_quantity' => 5,
    ]);
})->throws(ValidationException::class);

it('validates selling price is non-negative', function (): void {
    ProductData::validateAndCreate([
        'name' => 'Test',
        'sku' => 'TEST',
        'unit' => 'piece',
        'selling_price' => -100,
        'alert_quantity' => 5,
    ]);
})->throws(ValidationException::class);

it('validates unit with enum', function (): void {
    $data = ProductData::from([
        'name' => 'Test Product',
        'sku' => 'TEST-001',
        'unit' => 'gram',
        'selling_price' => 500,
        'alert_quantity' => 10,
    ]);

    expect($data->unit)->toBeInstanceOf(ProductUnitEnum::class)
        ->and($data->unit)->toBe(ProductUnitEnum::GRAM);
});

it('accepts enum case values for unit', function (): void {
    $data = ProductData::from([
        'name' => 'Test Product',
        'sku' => 'TEST-001',
        'unit' => ProductUnitEnum::MILLILITER->value,
        'selling_price' => 500,
        'alert_quantity' => 10,
    ]);

    expect($data->unit)->toBe(ProductUnitEnum::MILLILITER);
});

it('can use enum directly for unit', function (): void {
    $data = ProductData::from([
        'name' => 'Test Product',
        'sku' => 'TEST-001',
        'unit' => ProductUnitEnum::PIECE,
        'selling_price' => 500,
        'alert_quantity' => 10,
    ]);

    expect($data->unit)->toBe(ProductUnitEnum::PIECE);
});

it('rejects invalid unit', function (): void {
    ProductData::validateAndCreate([
        'name' => 'Test Product',
        'sku' => 'TEST-001',
        'unit' => 'invalid-unit',
        'selling_price' => 500,
        'alert_quantity' => 10,
    ]);
})->throws(ValidationException::class);

it('uses default unit', function (): void {
    $data = ProductData::from([
        'name' => 'Test Product',
        'sku' => 'TEST-001',
        'selling_price' => 500,
        'alert_quantity' => 10,
    ]);

    expect($data->unit)->toBe(ProductUnitEnum::PIECE);
});

it('handles snake_case mapping for product', function (): void {
    $data = ProductData::validateAndCreate([
        'category_id' => 5,
        'name' => 'Test',
        'sku' => 'TEST',
        'unit' => 'piece',
        'selling_price' => 100,
        'alert_quantity' => 5,
        'is_active' => false,
    ]);

    expect($data->categoryId)->toBe(5)
        ->and($data->sellingPrice)->toBe(100)
        ->and($data->alertQuantity)->toBe(5)
        ->and($data->isActive)->toBeFalse();
});
