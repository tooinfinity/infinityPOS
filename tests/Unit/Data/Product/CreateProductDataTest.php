<?php

declare(strict_types=1);

use App\Data\Product\CreateProductData;
use Illuminate\Http\UploadedFile;

it('may be created with required fields', function (): void {
    $data = new CreateProductData(
        name: 'Test Product',
        sku: 'PRD-001',
        barcode: '9781234567890',
        unit_id: 1,
        category_id: null,
        brand_id: null,
        description: null,
        image: null,
        cost_price: 5000,
        selling_price: 7500,
        alert_quantity: 10,
        track_inventory: true,
        is_active: true,
    );

    expect($data)
        ->name->toBe('Test Product')
        ->sku->toBe('PRD-001')
        ->barcode->toBe('9781234567890')
        ->unit_id->toBe(1)
        ->category_id->toBeNull()
        ->brand_id->toBeNull()
        ->description->toBeNull()
        ->image->toBeNull()
        ->cost_price->toBe(5000)
        ->selling_price->toBe(7500)
        ->alert_quantity->toBe(10)
        ->track_inventory->toBeTrue()
        ->is_active->toBeTrue();
});

it('may be created with an uploaded file image', function (): void {
    $file = UploadedFile::fake()->image('product.jpg');

    $data = new CreateProductData(
        name: 'Test Product',
        sku: null,
        barcode: null,
        unit_id: 1,
        category_id: null,
        brand_id: null,
        description: null,
        image: $file,
        cost_price: 5000,
        selling_price: 7500,
        alert_quantity: 10,
        track_inventory: true,
        is_active: true,
    );

    expect($data->image)->toBeInstanceOf(UploadedFile::class);
});

it('may be created with a string image path', function (): void {
    $data = new CreateProductData(
        name: 'Test Product',
        sku: null,
        barcode: null,
        unit_id: 1,
        category_id: null,
        brand_id: null,
        description: null,
        image: 'products/test.jpg',
        cost_price: 5000,
        selling_price: 7500,
        alert_quantity: 10,
        track_inventory: true,
        is_active: true,
    );

    expect($data->image)->toBe('products/test.jpg');
});

it('may be created with category and brand', function (): void {
    $data = new CreateProductData(
        name: 'Test Product',
        sku: null,
        barcode: null,
        unit_id: 1,
        category_id: 5,
        brand_id: 3,
        description: null,
        image: null,
        cost_price: 5000,
        selling_price: 7500,
        alert_quantity: 10,
        track_inventory: true,
        is_active: true,
    );

    expect($data)
        ->category_id->toBe(5)
        ->brand_id->toBe(3);
});

it('may be created with description', function (): void {
    $data = new CreateProductData(
        name: 'Test Product',
        sku: null,
        barcode: null,
        unit_id: 1,
        category_id: null,
        brand_id: null,
        description: 'This is a test description',
        image: null,
        cost_price: 5000,
        selling_price: 7500,
        alert_quantity: 10,
        track_inventory: true,
        is_active: true,
    );

    expect($data->description)->toBe('This is a test description');
});
