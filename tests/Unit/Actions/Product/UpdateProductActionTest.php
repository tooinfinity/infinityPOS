<?php

declare(strict_types=1);

use App\Actions\Product\UpdateProductAction;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

it('may update a product name', function (): void {
    $product = Product::factory()->create(['name' => 'Old Product Name']);

    $action = resolve(UpdateProductAction::class);

    $updatedProduct = $action->handle($product, [
        'name' => 'New Product Name',
    ]);

    expect($updatedProduct->name)->toBe('New Product Name');
});

it('updates product pricing', function (): void {
    $product = Product::factory()->create([
        'cost_price' => 5000,
        'selling_price' => 7500,
    ]);

    $action = resolve(UpdateProductAction::class);

    $updatedProduct = $action->handle($product, [
        'cost_price' => 6000,
        'selling_price' => 9000,
    ]);

    expect($updatedProduct->cost_price)->toBe(6000)
        ->and($updatedProduct->selling_price)->toBe(9000);
});

it('updates product quantity and alert quantity', function (): void {
    $product = Product::factory()->create([
        'quantity' => 100,
        'alert_quantity' => 10,
    ]);

    $action = resolve(UpdateProductAction::class);

    $updatedProduct = $action->handle($product, [
        'quantity' => 200,
        'alert_quantity' => 20,
    ]);

    expect($updatedProduct->quantity)->toBe(200)
        ->and($updatedProduct->alert_quantity)->toBe(20);
});

it('updates product unit', function (): void {
    $product = Product::factory()->create();
    $newUnit = Unit::factory()->create();

    $action = resolve(UpdateProductAction::class);

    $updatedProduct = $action->handle($product, [
        'unit_id' => $newUnit->id,
    ]);

    expect($updatedProduct->unit_id)->toBe($newUnit->id);
});

it('updates product category', function (): void {
    $product = Product::factory()->create();
    $newCategory = Category::factory()->create();

    $action = resolve(UpdateProductAction::class);

    $updatedProduct = $action->handle($product, [
        'category_id' => $newCategory->id,
    ]);

    expect($updatedProduct->category_id)->toBe($newCategory->id);
});

it('updates product brand', function (): void {
    $product = Product::factory()->create();
    $newBrand = Brand::factory()->create();

    $action = resolve(UpdateProductAction::class);

    $updatedProduct = $action->handle($product, [
        'brand_id' => $newBrand->id,
    ]);

    expect($updatedProduct->brand_id)->toBe($newBrand->id);
});

it('removes category by setting to null', function (): void {
    $product = Product::factory()->create(['category_id' => Category::factory()->create()->id]);

    $action = resolve(UpdateProductAction::class);

    $updatedProduct = $action->handle($product, [
        'category_id' => null,
    ]);

    expect($updatedProduct->category_id)->toBeNull();
});

it('removes brand by setting to null', function (): void {
    $product = Product::factory()->create(['brand_id' => Brand::factory()->create()->id]);

    $action = resolve(UpdateProductAction::class);

    $updatedProduct = $action->handle($product, [
        'brand_id' => null,
    ]);

    expect($updatedProduct->brand_id)->toBeNull();
});

it('updates product description', function (): void {
    $product = Product::factory()->create(['description' => 'Old description']);

    $action = resolve(UpdateProductAction::class);

    $updatedProduct = $action->handle($product, [
        'description' => 'New description',
    ]);

    expect($updatedProduct->description)->toBe('New description');
});

it('updates track_inventory status', function (): void {
    $product = Product::factory()->create(['track_inventory' => true]);

    $action = resolve(UpdateProductAction::class);

    $updatedProduct = $action->handle($product, [
        'track_inventory' => false,
    ]);

    expect($updatedProduct->track_inventory)->toBeFalse();
});

it('updates is_active status', function (): void {
    $product = Product::factory()->create(['is_active' => true]);

    $action = resolve(UpdateProductAction::class);

    $updatedProduct = $action->handle($product, [
        'is_active' => false,
    ]);

    expect($updatedProduct->is_active)->toBeFalse();
});

it('updates image with string path', function (): void {
    Storage::disk('public')->put('products/old-image.jpg', 'fake-content');

    $product = Product::factory()->create(['image' => 'products/old-image.jpg']);

    $action = resolve(UpdateProductAction::class);

    $updatedProduct = $action->handle($product, [
        'image' => 'products/new-image.jpg',
    ]);

    expect($updatedProduct->image)->toBe('products/new-image.jpg')
        ->and(Storage::disk('public')->exists('products/old-image.jpg'))->toBeFalse();
});

it('updates image with uploaded file', function (): void {
    Storage::disk('public')->put('products/old-image.jpg', 'fake-content');

    $product = Product::factory()->create(['image' => 'products/old-image.jpg']);

    $action = resolve(UpdateProductAction::class);

    $file = UploadedFile::fake()->image('product.png', 800, 600);

    $updatedProduct = $action->handle($product, [
        'image' => $file,
    ]);

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

    $action = resolve(UpdateProductAction::class);

    $updatedProduct = $action->handle($product, [
        'image' => null,
    ]);

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

    $action = resolve(UpdateProductAction::class);

    $updatedProduct = $action->handle($product, [
        'name' => 'New Name',
        'cost_price' => 6000,
        'selling_price' => 9000,
        'unit_id' => $newUnit->id,
    ]);

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

    $action = resolve(UpdateProductAction::class);

    $updatedProduct = $action->handle($product, [
        'cost_price' => 6000,
    ]);

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

    $action = resolve(UpdateProductAction::class);

    try {
        $action->handle($product, [
            'sku' => 'PRD-DUPLICATE',
        ]);
    } catch (Throwable) {
        // Expected to fail due to unique constraint
    }

    expect($product->fresh()->sku)->toBe('PRD-ORIGINAL');
});

it('does not delete image when string path is unchanged', function (): void {
    Storage::disk('public')->put('products/existing-image.jpg', 'fake-content');

    $product = Product::factory()->create(['image' => 'products/existing-image.jpg']);

    $action = resolve(UpdateProductAction::class);

    $updatedProduct = $action->handle($product, [
        'image' => 'products/existing-image.jpg',
    ]);

    expect($updatedProduct->image)->toBe('products/existing-image.jpg')
        ->and(Storage::disk('public')->exists('products/existing-image.jpg'))->toBeTrue();
});

it('does not delete image when string path is empty', function (): void {
    Storage::disk('public')->put('products/existing-image.jpg', 'fake-content');

    $product = Product::factory()->create(['image' => 'products/existing-image.jpg']);

    $action = resolve(UpdateProductAction::class);

    $updatedProduct = $action->handle($product, [
        'image' => '',
    ]);

    expect($updatedProduct->image)->toBe('')
        ->and(Storage::disk('public')->exists('products/existing-image.jpg'))->toBeTrue();
});
