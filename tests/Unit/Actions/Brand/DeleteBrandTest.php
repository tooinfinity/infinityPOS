<?php

declare(strict_types=1);

use App\Actions\Brand\DeleteBrand;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

it('may delete a brand', function (): void {
    $brand = Brand::factory()->create();

    $action = resolve(DeleteBrand::class);

    $result = $action->handle($brand);

    expect($result)->toBeTrue()
        ->and($brand->exists)->toBeFalse();
});

it('nullifies brand_id on associated products when deleting', function (): void {
    $brand = Brand::factory()->create();
    $product = Product::factory()->create([
        'brand_id' => $brand->id,
    ]);

    expect($product->brand_id)->toBe($brand->id);

    $action = resolve(DeleteBrand::class);
    $action->handle($brand);

    expect($product->refresh()->brand_id)->toBeNull();
});

it('nullifies brand_id on multiple associated products when deleting', function (): void {
    $brand = Brand::factory()->create();
    $products = Product::factory()->count(3)->create([
        'brand_id' => $brand->id,
    ]);

    $action = resolve(DeleteBrand::class);
    $action->handle($brand);

    foreach ($products as $product) {
        expect($product->refresh()->brand_id)->toBeNull();
    }
});

it('deletes brand without products', function (): void {
    $brand = Brand::factory()->create();

    $action = resolve(DeleteBrand::class);

    $result = $action->handle($brand);

    expect($result)->toBeTrue()
        ->and(Brand::query()->find($brand->id))->toBeNull();
});

it('deletes logo file when deleting brand', function (): void {
    Storage::disk('public')->put('brands/test-logo.webp', 'fake-content');

    $brand = Brand::factory()->create([
        'logo' => 'brands/test-logo.webp',
    ]);

    expect(Storage::disk('public')->exists('brands/test-logo.webp'))->toBeTrue();

    $action = resolve(DeleteBrand::class);
    $action->handle($brand);

    expect(Storage::disk('public')->exists('brands/test-logo.webp'))->toBeFalse();
});

it('deletes brand without logo', function (): void {
    $brand = Brand::factory()->create([
        'logo' => null,
    ]);

    $action = resolve(DeleteBrand::class);

    $result = $action->handle($brand);

    expect($result)->toBeTrue()
        ->and(Brand::query()->find($brand->id))->toBeNull();
});
