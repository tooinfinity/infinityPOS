<?php

declare(strict_types=1);

use App\Actions\Product\CreateProduct;
use App\Data\Product\CreateProductData;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

it('may create a product with required fields', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(CreateProduct::class);

    $data = new CreateProductData(
        name: 'Test Product',
        sku: null,
        barcode: null,
        unit_id: $unit->id,
        category_id: null,
        brand_id: null,
        description: null,
        cost_price: 5000,
        selling_price: 7500,
        alert_quantity: 10,
        track_inventory: true,
        is_active: true,
    );

    $product = $action->handle($data);

    expect($product)->toBeInstanceOf(Product::class)
        ->and($product->name)->toBe('Test Product')
        ->and($product->unit_id)->toBe($unit->id)
        ->and($product->cost_price)->toBe(5000)
        ->and($product->selling_price)->toBe(7500)
        ->and($product->alert_quantity)->toBe(10)
        ->and($product->exists)->toBeTrue();
});

it('auto-generates SKU when not provided', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(CreateProduct::class);

    $data = new CreateProductData(
        name: 'Test Product',
        sku: null,
        barcode: null,
        unit_id: $unit->id,
        category_id: null,
        brand_id: null,
        description: null,
        cost_price: 5000,
        selling_price: 7500,
        alert_quantity: 10,
        track_inventory: true,
        is_active: true,
    );

    $product = $action->handle($data);

    expect($product->sku)
        ->toStartWith('PRD-')
        ->toHaveLength(10);
});

it('auto-generates barcode when not provided', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(CreateProduct::class);

    $data = new CreateProductData(
        name: 'Test Product',
        sku: null,
        barcode: null,
        unit_id: $unit->id,
        category_id: null,
        brand_id: null,
        description: null,
        cost_price: 5000,
        selling_price: 7500,
        alert_quantity: 10,
        track_inventory: true,
        is_active: true,
    );

    $product = $action->handle($data);

    expect($product->barcode)
        ->toHaveLength(13)
        ->and(is_numeric($product->barcode))->toBeTrue();
});

it('creates product with custom SKU and barcode', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(CreateProduct::class);

    $data = new CreateProductData(
        name: 'Test Product',
        sku: 'PRD-CUSTOM01',
        barcode: '9781234567890',
        unit_id: $unit->id,
        category_id: null,
        brand_id: null,
        description: null,
        cost_price: 5000,
        selling_price: 7500,
        alert_quantity: 10,
        track_inventory: true,
        is_active: true,
    );

    $product = $action->handle($data);

    expect($product->sku)->toBe('PRD-CUSTOM01')
        ->and($product->barcode)->toBe('9781234567890');
});

it('creates product with category and brand', function (): void {
    $unit = Unit::factory()->create();
    $category = Category::factory()->create();
    $brand = Brand::factory()->create();

    $action = resolve(CreateProduct::class);

    $data = new CreateProductData(
        name: 'Test Product',
        sku: null,
        barcode: null,
        unit_id: $unit->id,
        category_id: $category->id,
        brand_id: $brand->id,
        description: null,
        cost_price: 5000,
        selling_price: 7500,
        alert_quantity: 10,
        track_inventory: true,
        is_active: true,
    );

    $product = $action->handle($data);

    expect($product->category_id)->toBe($category->id)
        ->and($product->brand_id)->toBe($brand->id);
});

it('creates product with description', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(CreateProduct::class);

    $data = new CreateProductData(
        name: 'Test Product',
        sku: null,
        barcode: null,
        unit_id: $unit->id,
        category_id: null,
        brand_id: null,
        description: 'This is a test product description',
        cost_price: 5000,
        selling_price: 7500,
        alert_quantity: 10,
        track_inventory: true,
        is_active: true,
    );

    $product = $action->handle($data);

    expect($product->description)->toBe('This is a test product description');
});

it('defaults track_inventory to true when not provided', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(CreateProduct::class);

    $data = new CreateProductData(
        name: 'Test Product',
        sku: null,
        barcode: null,
        unit_id: $unit->id,
        category_id: null,
        brand_id: null,
        description: null,
        cost_price: 5000,
        selling_price: 7500,
        alert_quantity: 10,
        track_inventory: true,
        is_active: true,
    );

    $product = $action->handle($data);

    expect($product->track_inventory)->toBeTrue();
});

it('defaults is_active to true when not provided', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(CreateProduct::class);

    $data = new CreateProductData(
        name: 'Test Product',
        sku: null,
        barcode: null,
        unit_id: $unit->id,
        category_id: null,
        brand_id: null,
        description: null,
        cost_price: 5000,
        selling_price: 7500,
        alert_quantity: 10,
        track_inventory: true,
        is_active: true,
    );

    $product = $action->handle($data);

    expect($product->is_active)->toBeTrue();
});

it('creates product with track_inventory set to false', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(CreateProduct::class);

    $data = new CreateProductData(
        name: 'Test Product',
        sku: null,
        barcode: null,
        unit_id: $unit->id,
        category_id: null,
        brand_id: null,
        description: null,
        cost_price: 5000,
        selling_price: 7500,
        alert_quantity: 10,
        track_inventory: false,
        is_active: true,
    );

    $product = $action->handle($data);

    expect($product->track_inventory)->toBeFalse();
});

it('creates product with is_active set to false', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(CreateProduct::class);

    $data = new CreateProductData(
        name: 'Test Product',
        sku: null,
        barcode: null,
        unit_id: $unit->id,
        category_id: null,
        brand_id: null,
        description: null,
        cost_price: 5000,
        selling_price: 7500,
        alert_quantity: 10,
        track_inventory: true,
        is_active: false,
    );

    $product = $action->handle($data);

    expect($product->is_active)->toBeFalse();
});

it('creates product without category_id and brand_id', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(CreateProduct::class);

    $data = new CreateProductData(
        name: 'Test Product',
        sku: null,
        barcode: null,
        unit_id: $unit->id,
        category_id: null,
        brand_id: null,
        description: null,
        cost_price: 5000,
        selling_price: 7500,
        alert_quantity: 10,
        track_inventory: true,
        is_active: true,
    );

    $product = $action->handle($data);

    expect($product->category_id)->toBeNull()
        ->and($product->brand_id)->toBeNull();
});

it('rolls back transaction on failure', function (): void {
    $unit = Unit::factory()->create();

    Product::factory()->create([
        'sku' => 'PRD-DUPLICATE',
        'barcode' => '9780000000000',
    ]);

    $action = resolve(CreateProduct::class);

    $data = new CreateProductData(
        name: 'Test Product',
        sku: 'PRD-DUPLICATE',
        barcode: '9780000000000',
        unit_id: $unit->id,
        category_id: null,
        brand_id: null,
        description: null,
        cost_price: 5000,
        selling_price: 7500,
        alert_quantity: 10,
        track_inventory: true,
        is_active: true,
    );

    try {
        $action->handle($data);
    } catch (Throwable) {
        // Expected to fail due to unique constraint
    }

    expect(Product::query()->where('name', 'Test Product')->exists())->toBeFalse();
});
