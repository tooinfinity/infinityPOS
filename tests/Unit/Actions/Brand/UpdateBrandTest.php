<?php

declare(strict_types=1);

use App\Actions\Brand\UpdateBrand;
use App\Data\Brand\BrandData;
use App\Models\Brand;

it('may update a brand name', function (): void {
    $brand = Brand::factory()->create([
        'name' => 'Old Name',
    ]);

    $action = resolve(UpdateBrand::class);

    $data = new BrandData(
        name: 'New Name',
        is_active: true,
    );

    $updatedBrand = $action->handle($brand, $data);

    expect($updatedBrand->name)->toBe('New Name');
});

it('updates is_active status', function (): void {
    $brand = Brand::factory()->create([
        'is_active' => true,
    ]);

    $action = resolve(UpdateBrand::class);

    $data = new BrandData(
        name: $brand->name,
        is_active: false,
    );

    $updatedBrand = $action->handle($brand, $data);

    expect($updatedBrand->is_active)->toBeFalse();
});
