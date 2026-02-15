<?php

declare(strict_types=1);

use App\Actions\Brand\UpdateBrand;
use App\Data\Brand\UpdateBrandData;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelData\Optional;

beforeEach(function (): void {
    Storage::fake('public');
});

it('may update a brand name', function (): void {
    $brand = Brand::factory()->create([
        'name' => 'Old Name',
        'slug' => 'old-name',
    ]);

    $action = resolve(UpdateBrand::class);

    $data = new UpdateBrandData(
        name: 'New Name',
        slug: Optional::create(),
        logo: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedBrand = $action->handle($brand, $data);

    expect($updatedBrand->name)->toBe('New Name')
        ->and($updatedBrand->slug)->toBe('new-name');
});

it('updates slug when name changes and no slug provided', function (): void {
    $brand = Brand::factory()->create([
        'name' => 'Old Name',
        'slug' => 'old-name',
    ]);

    $action = resolve(UpdateBrand::class);

    $data = new UpdateBrandData(
        name: 'New Name',
        slug: Optional::create(),
        logo: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedBrand = $action->handle($brand, $data);

    expect($updatedBrand->slug)->toBe('new-name');
});

it('keeps existing slug when name changes but slug is provided', function (): void {
    $brand = Brand::factory()->create([
        'name' => 'Old Name',
        'slug' => 'custom-slug',
    ]);

    $action = resolve(UpdateBrand::class);

    $data = new UpdateBrandData(
        name: 'New Name',
        slug: 'custom-slug',
        logo: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedBrand = $action->handle($brand, $data);

    expect($updatedBrand->slug)->toBe('custom-slug');
});

it('generates unique slug when updating to existing slug', function (): void {
    Brand::factory()->create([
        'name' => 'Existing Brand',
        'slug' => 'existing-slug',
    ]);

    $brand = Brand::factory()->create([
        'name' => 'Another Brand',
        'slug' => 'another-slug',
    ]);

    $action = resolve(UpdateBrand::class);

    $data = new UpdateBrandData(
        name: Optional::create(),
        slug: 'existing-slug',
        logo: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedBrand = $action->handle($brand, $data);

    expect($updatedBrand->slug)->toBe('existing-slug-1');
});

it('allows keeping own slug unchanged', function (): void {
    $brand = Brand::factory()->create([
        'name' => 'Test Brand',
        'slug' => 'test-slug',
    ]);

    $action = resolve(UpdateBrand::class);

    $data = new UpdateBrandData(
        name: 'Updated Brand',
        slug: 'test-slug',
        logo: Optional::create(),
        is_active: Optional::create(),
    );

    $updatedBrand = $action->handle($brand, $data);

    expect($updatedBrand->slug)->toBe('test-slug');
});

it('updates logo with uploaded file', function (): void {
    Storage::disk('public')->put('brands/old-logo.webp', 'fake-content');

    $brand = Brand::factory()->create([
        'logo' => 'brands/old-logo.webp',
    ]);

    $action = resolve(UpdateBrand::class);

    $file = UploadedFile::fake()->image('logo.png', 800, 600);

    $data = new UpdateBrandData(
        name: Optional::create(),
        slug: Optional::create(),
        logo: $file,
        is_active: Optional::create(),
    );

    $updatedBrand = $action->handle($brand, $data);

    expect($updatedBrand->logo)
        ->toStartWith('brands/')
        ->toEndWith('.webp')
        ->not->toBe('brands/old-logo.webp');
    expect(Storage::disk('public')->exists('brands/old-logo.webp'))->toBeFalse();
    expect(Storage::disk('public')->exists($updatedBrand->logo))->toBeTrue();
});

it('updates is_active status', function (): void {
    $brand = Brand::factory()->create([
        'is_active' => true,
    ]);

    $action = resolve(UpdateBrand::class);

    $data = new UpdateBrandData(
        name: Optional::create(),
        slug: Optional::create(),
        logo: Optional::create(),
        is_active: false,
    );

    $updatedBrand = $action->handle($brand, $data);

    expect($updatedBrand->is_active)->toBeFalse();
});

it('removes logo when set to null', function (): void {
    Storage::disk('public')->put('brands/old-logo.webp', 'fake-content');

    $brand = Brand::factory()->create([
        'logo' => 'brands/old-logo.webp',
    ]);

    expect(Storage::disk('public')->exists('brands/old-logo.webp'))->toBeTrue();

    $action = resolve(UpdateBrand::class);

    $data = new UpdateBrandData(
        name: Optional::create(),
        slug: Optional::create(),
        logo: null,
        is_active: Optional::create(),
    );

    $updatedBrand = $action->handle($brand, $data);

    expect($updatedBrand->logo)->toBeNull()
        ->and(Storage::disk('public')->exists('brands/old-logo.webp'))->toBeFalse();
});
