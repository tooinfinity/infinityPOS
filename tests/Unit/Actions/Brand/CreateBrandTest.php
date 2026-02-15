<?php

declare(strict_types=1);

use App\Actions\Brand\CreateBrand;
use App\Data\Brand\CreateBrandData;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function (): void {
    Storage::fake('public');
});

it('may create a brand', function (): void {
    $action = resolve(CreateBrand::class);

    $data = new CreateBrandData(
        name: 'Test Brand',
        slug: null,
        logo: null,
        is_active: true,
    );

    $brand = $action->handle($data);

    expect($brand)->toBeInstanceOf(Brand::class)
        ->and($brand->name)->toBe('Test Brand')
        ->and($brand->slug)->toBe('test-brand')
        ->and($brand->exists)->toBeTrue();
});

it('creates brand with custom slug', function (): void {
    $action = resolve(CreateBrand::class);

    $data = new CreateBrandData(
        name: 'Test Brand',
        slug: 'custom-slug',
        logo: null,
        is_active: true,
    );

    $brand = $action->handle($data);

    expect($brand->slug)->toBe('custom-slug');
});

it('generates unique slug when duplicate exists', function (): void {
    Brand::factory()->create([
        'name' => 'Test Brand',
        'slug' => 'test-brand',
    ]);

    $action = resolve(CreateBrand::class);

    $data = new CreateBrandData(
        name: 'Test Brand',
        slug: null,
        logo: null,
        is_active: true,
    );

    $brand = $action->handle($data);

    expect($brand->slug)->toBe('test-brand-1');
});

it('creates brand with string logo path', function (): void {
    $action = resolve(CreateBrand::class);

    $data = new CreateBrandData(
        name: 'Test Brand',
        slug: null,
        logo: 'brands/test-logo.png',
        is_active: true,
    );

    $brand = $action->handle($data);

    expect($brand->logo)->toBe('brands/test-logo.png');
});

it('creates brand with uploaded file logo', function (): void {
    $action = resolve(CreateBrand::class);

    $file = UploadedFile::fake()->image('logo.png', 800, 600);

    $data = new CreateBrandData(
        name: 'Test Brand',
        slug: null,
        logo: $file,
        is_active: true,
    );

    $brand = $action->handle($data);

    expect($brand->logo)
        ->toStartWith('brands/')
        ->toEndWith('.webp');
    expect(Storage::disk('public')->exists($brand->logo))->toBeTrue();
});

it('creates brand with is_active flag', function (): void {
    $action = resolve(CreateBrand::class);

    $data = new CreateBrandData(
        name: 'Test Brand',
        slug: null,
        logo: null,
        is_active: false,
    );

    $brand = $action->handle($data);

    expect($brand->is_active)->toBeFalse();
});

it('generates slug from name when not provided', function (): void {
    $action = resolve(CreateBrand::class);

    $data = new CreateBrandData(
        name: 'My Special Brand',
        slug: null,
        logo: null,
        is_active: true,
    );

    $brand = $action->handle($data);

    expect($brand->slug)->toBe(Str::slug('My Special Brand'));
});

it('defaults is_active to true when not provided', function (): void {
    $action = resolve(CreateBrand::class);

    $data = new CreateBrandData(
        name: 'Test Brand',
        slug: null,
        logo: null,
        is_active: true,
    );

    $brand = $action->handle($data);

    expect($brand->is_active)->toBeTrue();
});
