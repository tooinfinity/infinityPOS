<?php

declare(strict_types=1);

use App\Actions\Product\CreateProductAction;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

it('may create a product with required fields', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(CreateProductAction::class);

    $product = $action->handle([
        'name' => 'Test Product',
        'unit_id' => $unit->id,
        'cost_price' => 5000,
        'selling_price' => 7500,
        'quantity' => 100,
        'alert_quantity' => 10,
    ]);

    expect($product)->toBeInstanceOf(Product::class)
        ->and($product->name)->toBe('Test Product')
        ->and($product->unit_id)->toBe($unit->id)
        ->and($product->cost_price)->toBe(5000)
        ->and($product->selling_price)->toBe(7500)
        ->and($product->quantity)->toBe(100)
        ->and($product->alert_quantity)->toBe(10)
        ->and($product->exists)->toBeTrue();
});

it('auto-generates SKU when not provided', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(CreateProductAction::class);

    $product = $action->handle([
        'name' => 'Test Product',
        'unit_id' => $unit->id,
        'cost_price' => 5000,
        'selling_price' => 7500,
        'quantity' => 100,
        'alert_quantity' => 10,
    ]);

    expect($product->sku)
        ->toStartWith('PRD-')
        ->toHaveLength(10);
});

it('auto-generates barcode when not provided', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(CreateProductAction::class);

    $product = $action->handle([
        'name' => 'Test Product',
        'unit_id' => $unit->id,
        'cost_price' => 5000,
        'selling_price' => 7500,
        'quantity' => 100,
        'alert_quantity' => 10,
    ]);

    expect($product->barcode)
        ->toHaveLength(13)
        ->and(is_numeric($product->barcode))->toBeTrue();
});

it('creates product with custom SKU and barcode', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(CreateProductAction::class);

    $product = $action->handle([
        'name' => 'Test Product',
        'sku' => 'PRD-CUSTOM01',
        'barcode' => '9781234567890',
        'unit_id' => $unit->id,
        'cost_price' => 5000,
        'selling_price' => 7500,
        'quantity' => 100,
        'alert_quantity' => 10,
    ]);

    expect($product->sku)->toBe('PRD-CUSTOM01')
        ->and($product->barcode)->toBe('9781234567890');
});

it('creates product with category and brand', function (): void {
    $unit = Unit::factory()->create();
    $category = Category::factory()->create();
    $brand = Brand::factory()->create();

    $action = resolve(CreateProductAction::class);

    $product = $action->handle([
        'name' => 'Test Product',
        'unit_id' => $unit->id,
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'cost_price' => 5000,
        'selling_price' => 7500,
        'quantity' => 100,
        'alert_quantity' => 10,
    ]);

    expect($product->category_id)->toBe($category->id)
        ->and($product->brand_id)->toBe($brand->id);
});

it('creates product with description', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(CreateProductAction::class);

    $product = $action->handle([
        'name' => 'Test Product',
        'unit_id' => $unit->id,
        'description' => 'This is a test product description',
        'cost_price' => 5000,
        'selling_price' => 7500,
        'quantity' => 100,
        'alert_quantity' => 10,
    ]);

    expect($product->description)->toBe('This is a test product description');
});

it('creates product with uploaded image', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(CreateProductAction::class);

    $file = UploadedFile::fake()->image('product.png', 800, 600);

    $product = $action->handle([
        'name' => 'Test Product',
        'unit_id' => $unit->id,
        'image' => $file,
        'cost_price' => 5000,
        'selling_price' => 7500,
        'quantity' => 100,
        'alert_quantity' => 10,
    ]);

    expect($product->image)
        ->toStartWith('products/')
        ->toEndWith('.jpg')
        ->and(Storage::disk('public')->exists($product->image))->toBeTrue();
});

it('creates product with string image path', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(CreateProductAction::class);

    $product = $action->handle([
        'name' => 'Test Product',
        'unit_id' => $unit->id,
        'image' => 'products/test-image.jpg',
        'cost_price' => 5000,
        'selling_price' => 7500,
        'quantity' => 100,
        'alert_quantity' => 10,
    ]);

    expect($product->image)->toBe('products/test-image.jpg');
});

it('defaults track_inventory to true when not provided', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(CreateProductAction::class);

    $product = $action->handle([
        'name' => 'Test Product',
        'unit_id' => $unit->id,
        'cost_price' => 5000,
        'selling_price' => 7500,
        'quantity' => 100,
        'alert_quantity' => 10,
    ]);

    expect($product->track_inventory)->toBeTrue();
});

it('defaults is_active to true when not provided', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(CreateProductAction::class);

    $product = $action->handle([
        'name' => 'Test Product',
        'unit_id' => $unit->id,
        'cost_price' => 5000,
        'selling_price' => 7500,
        'quantity' => 100,
        'alert_quantity' => 10,
    ]);

    expect($product->is_active)->toBeTrue();
});

it('creates product with track_inventory set to false', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(CreateProductAction::class);

    $product = $action->handle([
        'name' => 'Test Product',
        'unit_id' => $unit->id,
        'track_inventory' => false,
        'cost_price' => 5000,
        'selling_price' => 7500,
        'quantity' => 100,
        'alert_quantity' => 10,
    ]);

    expect($product->track_inventory)->toBeFalse();
});

it('creates product with is_active set to false', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(CreateProductAction::class);

    $product = $action->handle([
        'name' => 'Test Product',
        'unit_id' => $unit->id,
        'is_active' => false,
        'cost_price' => 5000,
        'selling_price' => 7500,
        'quantity' => 100,
        'alert_quantity' => 10,
    ]);

    expect($product->is_active)->toBeFalse();
});

it('creates product without category_id and brand_id', function (): void {
    $unit = Unit::factory()->create();

    $action = resolve(CreateProductAction::class);

    $product = $action->handle([
        'name' => 'Test Product',
        'unit_id' => $unit->id,
        'cost_price' => 5000,
        'selling_price' => 7500,
        'quantity' => 100,
        'alert_quantity' => 10,
    ]);

    expect($product->category_id)->toBeNull()
        ->and($product->brand_id)->toBeNull();
});

it('rolls back transaction on failure', function (): void {
    $unit = Unit::factory()->create();

    Product::factory()->create([
        'sku' => 'PRD-DUPLICATE',
        'barcode' => '9780000000000',
    ]);

    $action = resolve(CreateProductAction::class);

    try {
        $action->handle([
            'name' => 'Test Product',
            'sku' => 'PRD-DUPLICATE',
            'barcode' => '9780000000000',
            'unit_id' => $unit->id,
            'cost_price' => 5000,
            'selling_price' => 7500,
            'quantity' => 100,
            'alert_quantity' => 10,
        ]);
    } catch (Throwable) {
        // Expected to fail due to unique constraint
    }

    expect(Product::query()->where('name', 'Test Product')->exists())->toBeFalse();
});
