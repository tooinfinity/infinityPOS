<?php

declare(strict_types=1);

use App\Actions\Product\UpdateProduct;
use App\Data\Product\UpdateProductData;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelData\Optional;

beforeEach(function (): void {
    Storage::fake('public');
});

it('may update a product name', function (): void {
    $product = Product::factory()->create(['name' => 'Old Product Name']);

    $action = resolve(UpdateProduct::class);

    $data = new UpdateProductData(
        name: 'New Product Name',
        sku: Optional::create(),
        barcode: Optional::create(),
        unit_id: Optional::create(),
        category_id: Optional::create(),
        brand_id: Optional::create(),
        description: Optional::create(),
        image: Optional::create(),
        cost_price: Optional::create(),
        selling_price: Optional::create(),
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedProduct = $action->handle($product, $data);

    expect($updatedProduct->name)->toBe('New Product Name');
});

it('updates product pricing', function (): void {
    $product = Product::factory()->create([
        'cost_price' => 5000,
        'selling_price' => 7500,
    ]);

    $action = resolve(UpdateProduct::class);

    $data = new UpdateProductData(
        name: Optional::create(),
        sku: Optional::create(),
        barcode: Optional::create(),
        unit_id: Optional::create(),
        category_id: Optional::create(),
        brand_id: Optional::create(),
        description: Optional::create(),
        image: Optional::create(),
        cost_price: 6000,
        selling_price: 9000,
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedProduct = $action->handle($product, $data);

    expect($updatedProduct->cost_price)->toBe(6000)
        ->and($updatedProduct->selling_price)->toBe(9000);
});

it('updates product unit', function (): void {
    $product = Product::factory()->create();
    $newUnit = Unit::factory()->create();

    $action = resolve(UpdateProduct::class);

    $data = new UpdateProductData(
        name: Optional::create(),
        sku: Optional::create(),
        barcode: Optional::create(),
        unit_id: $newUnit->id,
        category_id: Optional::create(),
        brand_id: Optional::create(),
        description: Optional::create(),
        image: Optional::create(),
        cost_price: Optional::create(),
        selling_price: Optional::create(),
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedProduct = $action->handle($product, $data);

    expect($updatedProduct->unit_id)->toBe($newUnit->id);
});

it('updates product category', function (): void {
    $product = Product::factory()->create();
    $newCategory = Category::factory()->create();

    $action = resolve(UpdateProduct::class);

    $data = new UpdateProductData(
        name: Optional::create(),
        sku: Optional::create(),
        barcode: Optional::create(),
        unit_id: Optional::create(),
        category_id: $newCategory->id,
        brand_id: Optional::create(),
        description: Optional::create(),
        image: Optional::create(),
        cost_price: Optional::create(),
        selling_price: Optional::create(),
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedProduct = $action->handle($product, $data);

    expect($updatedProduct->category_id)->toBe($newCategory->id);
});

it('updates product brand', function (): void {
    $product = Product::factory()->create();
    $newBrand = Brand::factory()->create();

    $action = resolve(UpdateProduct::class);

    $data = new UpdateProductData(
        name: Optional::create(),
        sku: Optional::create(),
        barcode: Optional::create(),
        unit_id: Optional::create(),
        category_id: Optional::create(),
        brand_id: $newBrand->id,
        description: Optional::create(),
        image: Optional::create(),
        cost_price: Optional::create(),
        selling_price: Optional::create(),
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedProduct = $action->handle($product, $data);

    expect($updatedProduct->brand_id)->toBe($newBrand->id);
});

it('removes category by setting to null', function (): void {
    $product = Product::factory()->create(['category_id' => Category::factory()->create()->id]);

    $action = resolve(UpdateProduct::class);

    $data = new UpdateProductData(
        name: Optional::create(),
        sku: Optional::create(),
        barcode: Optional::create(),
        unit_id: Optional::create(),
        category_id: null,
        brand_id: Optional::create(),
        description: Optional::create(),
        image: Optional::create(),
        cost_price: Optional::create(),
        selling_price: Optional::create(),
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedProduct = $action->handle($product, $data);

    expect($updatedProduct->category_id)->toBeNull();
});

it('removes brand by setting to null', function (): void {
    $product = Product::factory()->create(['brand_id' => Brand::factory()->create()->id]);

    $action = resolve(UpdateProduct::class);

    $data = new UpdateProductData(
        name: Optional::create(),
        sku: Optional::create(),
        barcode: Optional::create(),
        unit_id: Optional::create(),
        category_id: Optional::create(),
        brand_id: null,
        description: Optional::create(),
        image: Optional::create(),
        cost_price: Optional::create(),
        selling_price: Optional::create(),
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedProduct = $action->handle($product, $data);

    expect($updatedProduct->brand_id)->toBeNull();
});

it('updates product description', function (): void {
    $product = Product::factory()->create(['description' => 'Old description']);

    $action = resolve(UpdateProduct::class);

    $data = new UpdateProductData(
        name: Optional::create(),
        sku: Optional::create(),
        barcode: Optional::create(),
        unit_id: Optional::create(),
        category_id: Optional::create(),
        brand_id: Optional::create(),
        description: 'New description',
        image: Optional::create(),
        cost_price: Optional::create(),
        selling_price: Optional::create(),
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedProduct = $action->handle($product, $data);

    expect($updatedProduct->description)->toBe('New description');
});

it('updates track_inventory status', function (): void {
    $product = Product::factory()->create(['track_inventory' => true]);

    $action = resolve(UpdateProduct::class);

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
        alert_quantity: Optional::create(),
        track_inventory: false,
        is_active: Optional::create(),
    );

    $updatedProduct = $action->handle($product, $data);

    expect($updatedProduct->track_inventory)->toBeFalse();
});

it('updates is_active status', function (): void {
    $product = Product::factory()->create(['is_active' => true]);

    $action = resolve(UpdateProduct::class);

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
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: false,
    );

    $updatedProduct = $action->handle($product, $data);

    expect($updatedProduct->is_active)->toBeFalse();
});

it('updates image with string path', function (): void {
    Storage::disk('public')->put('products/old-image.jpg', 'fake-content');

    $product = Product::factory()->create(['image' => 'products/old-image.jpg']);

    $action = resolve(UpdateProduct::class);

    $data = new UpdateProductData(
        name: Optional::create(),
        sku: Optional::create(),
        barcode: Optional::create(),
        unit_id: Optional::create(),
        category_id: Optional::create(),
        brand_id: Optional::create(),
        description: Optional::create(),
        image: 'products/new-image.jpg',
        cost_price: Optional::create(),
        selling_price: Optional::create(),
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedProduct = $action->handle($product, $data);

    expect($updatedProduct->image)->toBe('products/new-image.jpg')
        ->and(Storage::disk('public')->exists('products/old-image.jpg'))->toBeFalse();
});

it('updates image with uploaded file', function (): void {
    Storage::disk('public')->put('products/old-image.jpg', 'fake-content');

    $product = Product::factory()->create(['image' => 'products/old-image.jpg']);

    $action = resolve(UpdateProduct::class);

    $file = UploadedFile::fake()->image('product.png', 800, 600);

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
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedProduct = $action->handle($product, $data);

    expect($updatedProduct->image)
        ->toStartWith('products/')
        ->toEndWith('.webp')
        ->not->toBe('products/old-image.jpg');
    expect(Storage::disk('public')->exists('products/old-image.jpg'))->toBeFalse();
    expect(Storage::disk('public')->exists($updatedProduct->image))->toBeTrue();
});

it('removes image when set to null', function (): void {
    Storage::disk('public')->put('products/old-image.jpg', 'fake-content');

    $product = Product::factory()->create(['image' => 'products/old-image.jpg']);

    expect(Storage::disk('public')->exists('products/old-image.jpg'))->toBeTrue();

    $action = resolve(UpdateProduct::class);

    $data = new UpdateProductData(
        name: Optional::create(),
        sku: Optional::create(),
        barcode: Optional::create(),
        unit_id: Optional::create(),
        category_id: Optional::create(),
        brand_id: Optional::create(),
        description: Optional::create(),
        image: null,
        cost_price: Optional::create(),
        selling_price: Optional::create(),
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedProduct = $action->handle($product, $data);

    expect($updatedProduct->image)->toBeNull()
        ->and(Storage::disk('public')->exists('products/old-image.jpg'))->toBeFalse();
});

it('updates multiple fields at once', function (): void {
    $product = Product::factory()->create([
        'name' => 'Old Name',
        'cost_price' => 5000,
        'selling_price' => 7500,
    ]);
    $newUnit = Unit::factory()->create();

    $action = resolve(UpdateProduct::class);

    $data = new UpdateProductData(
        name: 'New Name',
        sku: Optional::create(),
        barcode: Optional::create(),
        unit_id: $newUnit->id,
        category_id: Optional::create(),
        brand_id: Optional::create(),
        description: Optional::create(),
        image: Optional::create(),
        cost_price: 6000,
        selling_price: 9000,
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedProduct = $action->handle($product, $data);

    expect($updatedProduct->name)->toBe('New Name')
        ->and($updatedProduct->cost_price)->toBe(6000)
        ->and($updatedProduct->selling_price)->toBe(9000)
        ->and($updatedProduct->unit_id)->toBe($newUnit->id);
});

it('keeps unchanged fields intact', function (): void {
    $product = Product::factory()->create([
        'name' => 'Product Name',
        'sku' => 'PRD-TEST01',
        'barcode' => '9781234567890',
        'cost_price' => 5000,
    ]);

    $action = resolve(UpdateProduct::class);

    $data = new UpdateProductData(
        name: Optional::create(),
        sku: Optional::create(),
        barcode: Optional::create(),
        unit_id: Optional::create(),
        category_id: Optional::create(),
        brand_id: Optional::create(),
        description: Optional::create(),
        image: Optional::create(),
        cost_price: 6000,
        selling_price: Optional::create(),
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedProduct = $action->handle($product, $data);

    expect($updatedProduct->name)->toBe('Product Name')
        ->and($updatedProduct->sku)->toBe('PRD-TEST01')
        ->and($updatedProduct->barcode)->toBe('9781234567890')
        ->and($updatedProduct->cost_price)->toBe(6000);
});

it('rolls back transaction on failure', function (): void {
    $existingProduct = Product::factory()->create([
        'sku' => 'PRD-DUPLICATE',
    ]);
    $product = Product::factory()->create([
        'name' => 'Original Name',
        'sku' => 'PRD-ORIGINAL',
    ]);

    $action = resolve(UpdateProduct::class);

    $data = new UpdateProductData(
        name: Optional::create(),
        sku: 'PRD-DUPLICATE',
        barcode: Optional::create(),
        unit_id: Optional::create(),
        category_id: Optional::create(),
        brand_id: Optional::create(),
        description: Optional::create(),
        image: Optional::create(),
        cost_price: Optional::create(),
        selling_price: Optional::create(),
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    try {
        $action->handle($product, $data);
    } catch (Throwable) {
        // Expected to fail due to unique constraint
    }

    expect($product->fresh()->sku)->toBe('PRD-ORIGINAL');
});

it('does not delete image when string path is unchanged', function (): void {
    Storage::disk('public')->put('products/existing-image.jpg', 'fake-content');

    $product = Product::factory()->create(['image' => 'products/existing-image.jpg']);

    $action = resolve(UpdateProduct::class);

    $data = new UpdateProductData(
        name: Optional::create(),
        sku: Optional::create(),
        barcode: Optional::create(),
        unit_id: Optional::create(),
        category_id: Optional::create(),
        brand_id: Optional::create(),
        description: Optional::create(),
        image: 'products/existing-image.jpg',
        cost_price: Optional::create(),
        selling_price: Optional::create(),
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedProduct = $action->handle($product, $data);

    expect($updatedProduct->image)->toBe('products/existing-image.jpg')
        ->and(Storage::disk('public')->exists('products/existing-image.jpg'))->toBeTrue();
});

it('does not delete image when string path is empty', function (): void {
    Storage::disk('public')->put('products/existing-image.jpg', 'fake-content');

    $product = Product::factory()->create(['image' => 'products/existing-image.jpg']);

    $action = resolve(UpdateProduct::class);

    $data = new UpdateProductData(
        name: Optional::create(),
        sku: Optional::create(),
        barcode: Optional::create(),
        unit_id: Optional::create(),
        category_id: Optional::create(),
        brand_id: Optional::create(),
        description: Optional::create(),
        image: '',
        cost_price: Optional::create(),
        selling_price: Optional::create(),
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedProduct = $action->handle($product, $data);

    // Empty string image is ignored by the action, so image remains unchanged
    expect($updatedProduct->image)->toBe('products/existing-image.jpg')
        ->and(Storage::disk('public')->exists('products/existing-image.jpg'))->toBeTrue();
});

it('updates sku and barcode', function (): void {
    $product = Product::factory()->create([
        'sku' => 'OLD-SKU-001',
        'barcode' => '9780000000001',
    ]);

    $action = resolve(UpdateProduct::class);

    $data = new UpdateProductData(
        name: Optional::create(),
        sku: 'NEW-SKU-002',
        barcode: '9780000000002',
        unit_id: Optional::create(),
        category_id: Optional::create(),
        brand_id: Optional::create(),
        description: Optional::create(),
        image: Optional::create(),
        cost_price: Optional::create(),
        selling_price: Optional::create(),
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedProduct = $action->handle($product, $data);

    expect($updatedProduct->sku)->toBe('NEW-SKU-002')
        ->and($updatedProduct->barcode)->toBe('9780000000002');
});

it('updates alert quantity', function (): void {
    $product = Product::factory()->create(['alert_quantity' => 10]);

    $action = resolve(UpdateProduct::class);

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
        alert_quantity: 20,
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedProduct = $action->handle($product, $data);

    expect($updatedProduct->alert_quantity)->toBe(20);
});

it('updates image with string path when old image does not exist in storage', function (): void {
    $product = Product::factory()->create(['image' => 'products/non-existent-image.jpg']);

    // Ensure the old image file does NOT exist
    Storage::disk('public')->assertMissing('products/non-existent-image.jpg');

    $action = resolve(UpdateProduct::class);

    $data = new UpdateProductData(
        name: Optional::create(),
        sku: Optional::create(),
        barcode: Optional::create(),
        unit_id: Optional::create(),
        category_id: Optional::create(),
        brand_id: Optional::create(),
        description: Optional::create(),
        image: 'products/new-image.jpg',
        cost_price: Optional::create(),
        selling_price: Optional::create(),
        alert_quantity: Optional::create(),
        track_inventory: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedProduct = $action->handle($product, $data);

    // Should update to new image without error, even though old doesn't exist
    expect($updatedProduct->image)->toBe('products/new-image.jpg');
});
