<?php

declare(strict_types=1);

use App\Actions\Brand\UpdateBrand;
use App\Data\Brand\UpdateBrandData;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelData\Optional;

it('may update a brand name', function (): void {
    $brand = Brand::factory()->create([
        'name' => 'Old Name',
    ]);

    $action = resolve(UpdateBrand::class);

    $data = new UpdateBrandData(
        name: 'New Name',
        is_active: Optional::create(),
    );

    $updatedBrand = $action->handle($brand, $data);

    expect($updatedBrand->name)->toBe('New Name');
});

it('updates is_active status', function (): void {
    $brand = Brand::factory()->create([
        'is_active' => true,
    ]);

    $action = resolve(UpdateBrand::class);

    $data = new UpdateBrandData(
        name: Optional::create(),
        is_active: false,
    );

    $updatedBrand = $action->handle($brand, $data);

    expect($updatedBrand->is_active)->toBeFalse();
});
