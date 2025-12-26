<?php

declare(strict_types=1);

use App\Actions\Products\UpdateProduct;
use App\Data\Products\UpdateProductData;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;

it('may update a product', function (): void {
    $user = User::factory()->create();
    $category1 = Category::factory()->create(['created_by' => $user->id]);
    $brand1 = Brand::factory()->create(['created_by' => $user->id]);
    $product = Product::factory()->create([
        'sku' => 'OLD-001',
        'barcode' => '0000000000000',
        'name' => 'Old Product',
        'cost' => 1000,
        'price' => 2000,
        'category_id' => $category1->id,
        'brand_id' => $brand1->id,
        'has_batches' => false,
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $user2 = User::factory()->create();
    $category2 = Category::factory()->create(['created_by' => $user->id]);
    $brand2 = Brand::factory()->create(['created_by' => $user->id]);
    $unit = Unit::factory()->create(['created_by' => $user->id]);
    $action = resolve(UpdateProduct::class);

    $data = UpdateProductData::from([
        'sku' => 'NEW-001',
        'barcode' => '9999999999999',
        'name' => 'Updated Product',
        'description' => 'Updated description',
        'image' => 'updated.jpg',
        'category_id' => $category2->id,
        'brand_id' => $brand2->id,
        'unit_id' => $unit->id,
        'cost' => 7500,
        'price' => 15000,
        'alert_quantity' => 20,
        'has_batches' => true,
        'is_active' => false,
        'updated_by' => $user2->id,
    ]);

    $action->handle($product, $data);

    expect($product->refresh()->sku)->toBe('NEW-001')
        ->and($product->barcode)->toBe('9999999999999')
        ->and($product->name)->toBe('Updated Product')
        ->and($product->description)->toBe('Updated description')
        ->and($product->image)->toBe('updated.jpg')
        ->and($product->category_id)->toBe($category2->id)
        ->and($product->brand_id)->toBe($brand2->id)
        ->and($product->unit_id)->toBe($unit->id)
        ->and($product->cost)->toBe(7500)
        ->and($product->price)->toBe(15000)
        ->and($product->alert_quantity)->toBe(20)
        ->and($product->has_batches)->toBeTrue()
        ->and($product->is_active)->toBeFalse()
        ->and($product->updated_by)->toBe($user2->id);
});
