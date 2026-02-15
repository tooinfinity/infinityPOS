<?php

declare(strict_types=1);

use App\Data\Product\UpdateProductData;
use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Optional;

it('may be created with optional fields', function (): void {
    $data = new UpdateProductData(
        name: Optional::create(),
        sku: Optional::create(),
        barcode: Optional::create(),
        unit_id: Optional::create(),
        category_id: Optional::create(),
        brand_id: Optional::create(),
        description: Optional::create(),
        image: Optional::create(),
        cost_price: Optional::create(),
        selling_price: Optional::create(),
        quantity: Optional::create(),
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    expect($data->name)->toBeInstanceOf(Optional::class);
});

it('may be created with specific values', function (): void {
    $data = new UpdateProductData(
        name: 'Updated Product',
        sku: Optional::create(),
        barcode: Optional::create(),
        unit_id: Optional::create(),
        category_id: Optional::create(),
        brand_id: Optional::create(),
        description: Optional::create(),
        image: Optional::create(),
        cost_price: 6000,
        selling_price: Optional::create(),
        quantity: Optional::create(),
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    expect($data->name)->toBe('Updated Product')
        ->and($data->cost_price)->toBe(6000);
});

it('may be created with null values', function (): void {
    $data = new UpdateProductData(
        name: Optional::create(),
        sku: Optional::create(),
        barcode: Optional::create(),
        unit_id: Optional::create(),
        category_id: null,
        brand_id: null,
        description: Optional::create(),
        image: null,
        cost_price: Optional::create(),
        selling_price: Optional::create(),
        quantity: Optional::create(),
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    expect($data->category_id)->toBeNull()
        ->and($data->brand_id)->toBeNull()
        ->and($data->image)->toBeNull();
});

it('may be created with uploaded file image', function (): void {
    $file = UploadedFile::fake()->image('product.jpg');

    $data = new UpdateProductData(
        name: Optional::create(),
        sku: Optional::create(),
        barcode: Optional::create(),
        unit_id: Optional::create(),
        category_id: Optional::create(),
        brand_id: Optional::create(),
        description: Optional::create(),
        image: $file,
        cost_price: Optional::create(),
        selling_price: Optional::create(),
        quantity: Optional::create(),
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    expect($data->image)->toBeInstanceOf(UploadedFile::class);
});
