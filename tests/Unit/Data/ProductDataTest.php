<?php

declare(strict_types=1);

use App\Data\Brands\BrandData;
use App\Data\Categories\CategoryData;
use App\Data\Products\ProductData;
use App\Data\Taxes\TaxData;
use App\Data\Units\UnitData;
use App\Data\Users\UserData;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\User;

it('transforms a product model into ProductData', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();
    $category = Category::factory()->create();
    $brand = Brand::factory()->create();
    $unit = Unit::factory()->create();
    $tax = Tax::factory()->create();

    /** @var Product $product */
    $product = Product::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->for($category, 'category')
        ->for($brand, 'brand')
        ->for($unit, 'unit')
        ->for($tax, 'tax')
        ->create([
            'sku' => 'SKU-001',
            'barcode' => '1234567890123',
            'name' => 'Sample Product',
            'description' => 'A great product',
            'image' => null,
            'cost' => 1000,
            'price' => 1500,
            'alert_quantity' => 5,
            'has_batches' => true,
            'is_active' => true,
        ]);

    $data = ProductData::from(
        $product->load(['creator', 'updater', 'category', 'brand', 'unit', 'tax'])
    );

    expect($data)
        ->toBeInstanceOf(ProductData::class)
        ->id->toBe($product->id)
        ->sku->toBe('SKU-001')
        ->barcode->toBe('1234567890123')
        ->name->toBe('Sample Product')
        ->description->toBe('A great product')
        ->cost->toBe(1000)
        ->price->toBe(1500)
        ->alert_quantity->toBe(5)
        ->has_batches->toBeTrue()
        ->is_active->toBeTrue()
        ->and($data->category->resolve())
        ->toBeInstanceOf(CategoryData::class)
        ->id->toBe($category->id)
        ->and($data->brand->resolve())
        ->toBeInstanceOf(BrandData::class)
        ->id->toBe($brand->id)
        ->and($data->unit->resolve())
        ->toBeInstanceOf(UnitData::class)
        ->id->toBe($unit->id)
        ->and($data->tax->resolve())
        ->toBeInstanceOf(TaxData::class)
        ->id->toBe($tax->id)
        ->and($data->creator->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($creator->id)
        ->and($data->updater->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($updater->id)
        ->and($data->created_at)
        ->toBe($product->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($product->updated_at->toDateTimeString());
});
