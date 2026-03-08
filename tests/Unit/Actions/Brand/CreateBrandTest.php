<?php

declare(strict_types=1);

use App\Actions\Brand\CreateBrand;
use App\Data\Brand\BrandData;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

it('may create a brand', function (): void {
    $action = resolve(CreateBrand::class);

    $data = new BrandData(
        name: 'Test Brand',
        is_active: true,
    );

    $brand = $action->handle($data);

    expect($brand)->toBeInstanceOf(Brand::class)
        ->and($brand->name)->toBe('Test Brand')
        ->and($brand->exists)->toBeTrue();
});


it('creates brand with is_active flag', function (): void {
    $action = resolve(CreateBrand::class);

    $data = new BrandData(
        name: 'Test Brand',
        is_active: false,
    );

    $brand = $action->handle($data);

    expect($brand->is_active)->toBeFalse();
});

it('defaults is_active to true when not provided', function (): void {
    $action = resolve(CreateBrand::class);

    $data = new BrandData(
        name: 'Test Brand',
        is_active: true,
    );

    $brand = $action->handle($data);

    expect($brand->is_active)->toBeTrue();
});
