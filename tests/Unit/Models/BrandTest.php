<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

test('to array', function (): void {
    $brand = Brand::factory()->create()->refresh();

    expect(array_keys($brand->toArray()))
        ->toBe([
            'id',
            'name',
            'slug',
            'logo',
            'is_active',
            'created_at',
            'updated_at',
        ]);
});

test('only returns active brands by default', function (): void {
    Brand::factory()->count(2)->create([
        'is_active' => true,
    ]);
    Brand::factory()->count(2)->create([
        'is_active' => false,
    ]);

    $brands = Brand::all();

    expect($brands)
        ->toHaveCount(2);
});

test('brand has many products', function (): void {
    $brand = Brand::factory()->create()->refresh();
    Product::factory()->create([
        'brand_id' => $brand->id,
    ]);

    expect($brand->products)->toHaveCount(1);
});

test('logo url returns null when no logo', function (): void {
    $brand = Brand::factory()->create([
        'logo' => null,
    ]);

    expect($brand->logo_url)->toBeNull();
});

test('logo url returns full url when logo exists', function (): void {
    Storage::fake('public');

    $brand = Brand::factory()->create([
        'logo' => 'brands/test-logo.webp',
    ]);

    Storage::disk('public')->put('brands/test-logo.webp', 'fake-content');

    expect($brand->logo_url)
        ->not->toBeNull()
        ->toContain('brands/test-logo.webp');
});
