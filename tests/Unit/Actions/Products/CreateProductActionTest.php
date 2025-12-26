<?php

declare(strict_types=1);

use App\Actions\Products\CreateProduct;
use App\Data\Products\CreateProductData;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;

it('may create a product', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['created_by' => $user->id]);
    $brand = Brand::factory()->create(['created_by' => $user->id]);
    $unit = Unit::factory()->create(['created_by' => $user->id]);
    $action = resolve(CreateProduct::class);

    $data = CreateProductData::from([
        'sku' => 'PROD-001',
        'barcode' => '1234567890123',
        'name' => 'Test Product',
        'description' => 'A test product description',
        'image' => 'product.jpg',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'unit_id' => $unit->id,
        'cost' => 5000,
        'price' => 10000,
        'alert_quantity' => 10,
        'has_batches' => false,
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $product = $action->handle($data);

    expect($product)->toBeInstanceOf(Product::class)
        ->and($product->sku)->toBe('PROD-001')
        ->and($product->barcode)->toBe('1234567890123')
        ->and($product->name)->toBe('Test Product')
        ->and($product->description)->toBe('A test product description')
        ->and($product->image)->toBe('product.jpg')
        ->and($product->category_id)->toBe($category->id)
        ->and($product->brand_id)->toBe($brand->id)
        ->and($product->unit_id)->toBe($unit->id)
        ->and($product->cost)->toBe(5000)
        ->and($product->price)->toBe(10000)
        ->and($product->alert_quantity)->toBe(10)
        ->and($product->has_batches)->toBeFalse()
        ->and($product->is_active)->toBeTrue()
        ->and($product->created_by)->toBe($user->id);
});
