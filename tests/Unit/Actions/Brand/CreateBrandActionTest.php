<?php

declare(strict_types=1);

use App\Actions\Brand\CreateBrandAction;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function (): void {
    Storage::fake('public');
});

it('may create a brand', function (): void {
    $action = resolve(CreateBrandAction::class);

    $brand = $action->handle([
        'name' => 'Test Brand',
    ]);

    expect($brand)->toBeInstanceOf(Brand::class)
        ->and($brand->name)->toBe('Test Brand')
        ->and($brand->slug)->toBe('test-brand')
        ->and($brand->exists)->toBeTrue();
});

it('creates brand with custom slug', function (): void {
    $action = resolve(CreateBrandAction::class);

    $brand = $action->handle([
        'name' => 'Test Brand',
        'slug' => 'custom-slug',
    ]);

    expect($brand->slug)->toBe('custom-slug');
});

it('generates unique slug when duplicate exists', function (): void {
    Brand::factory()->create([
        'name' => 'Test Brand',
        'slug' => 'test-brand',
    ]);

    $action = resolve(CreateBrandAction::class);

    $brand = $action->handle([
        'name' => 'Test Brand',
    ]);

    expect($brand->slug)->toBe('test-brand-1');
});

it('creates brand with string logo path', function (): void {
    $action = resolve(CreateBrandAction::class);

    $brand = $action->handle([
        'name' => 'Test Brand',
        'logo' => 'brands/test-logo.png',
    ]);

    expect($brand->logo)->toBe('brands/test-logo.png');
});

it('creates brand with uploaded file logo', function (): void {
    $action = resolve(CreateBrandAction::class);

    $file = UploadedFile::fake()->image('logo.png', 800, 600);

    $brand = $action->handle([
        'name' => 'Test Brand',
        'logo' => $file,
    ]);

    expect($brand->logo)
        ->toStartWith('brands/')
        ->toEndWith('.webp');
    expect(Storage::disk('public')->exists($brand->logo))->toBeTrue();
});

it('creates brand with is_active flag', function (): void {
    $action = resolve(CreateBrandAction::class);

    $brand = $action->handle([
        'name' => 'Test Brand',
        'is_active' => false,
    ]);

    expect($brand->is_active)->toBeFalse();
});

it('generates slug from name when not provided', function (): void {
    $action = resolve(CreateBrandAction::class);

    $brand = $action->handle([
        'name' => 'My Special Brand',
    ]);

    expect($brand->slug)->toBe(Str::slug('My Special Brand'));
});

it('defaults is_active to true when not provided', function (): void {
    $action = resolve(CreateBrandAction::class);

    $brand = $action->handle([
        'name' => 'Test Brand',
    ]);

    expect($brand->is_active)->toBeTrue();
});
