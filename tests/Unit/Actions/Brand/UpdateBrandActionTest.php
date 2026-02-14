<?php

declare(strict_types=1);

use App\Actions\Brand\UpdateBrandAction;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

it('may update a brand name', function (): void {
    $brand = Brand::factory()->create([
        'name' => 'Old Name',
        'slug' => 'old-name',
    ]);

    $action = resolve(UpdateBrandAction::class);

    $updatedBrand = $action->handle($brand, [
        'name' => 'New Name',
    ]);

    expect($updatedBrand->name)->toBe('New Name')
        ->and($updatedBrand->slug)->toBe('new-name');
});

it('updates slug when name changes and no slug provided', function (): void {
    $brand = Brand::factory()->create([
        'name' => 'Old Name',
        'slug' => 'old-name',
    ]);

    $action = resolve(UpdateBrandAction::class);

    $updatedBrand = $action->handle($brand, [
        'name' => 'New Name',
    ]);

    expect($updatedBrand->slug)->toBe('new-name');
});

it('keeps existing slug when name changes but slug is provided', function (): void {
    $brand = Brand::factory()->create([
        'name' => 'Old Name',
        'slug' => 'custom-slug',
    ]);

    $action = resolve(UpdateBrandAction::class);

    $updatedBrand = $action->handle($brand, [
        'name' => 'New Name',
        'slug' => 'custom-slug',
    ]);

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

    $action = resolve(UpdateBrandAction::class);

    $updatedBrand = $action->handle($brand, [
        'slug' => 'existing-slug',
    ]);

    expect($updatedBrand->slug)->toBe('existing-slug-1');
});

it('allows keeping own slug unchanged', function (): void {
    $brand = Brand::factory()->create([
        'name' => 'Test Brand',
        'slug' => 'test-slug',
    ]);

    $action = resolve(UpdateBrandAction::class);

    $updatedBrand = $action->handle($brand, [
        'name' => 'Updated Brand',
        'slug' => 'test-slug',
    ]);

    expect($updatedBrand->slug)->toBe('test-slug');
});

it('updates logo with string path', function (): void {
    $brand = Brand::factory()->create([
        'logo' => 'old-logo.png',
    ]);

    $action = resolve(UpdateBrandAction::class);

    $updatedBrand = $action->handle($brand, [
        'logo' => 'new-logo.png',
    ]);

    expect($updatedBrand->logo)->toBe('new-logo.png');
});

it('updates logo with uploaded file', function (): void {
    Storage::disk('public')->put('brands/old-logo.webp', 'fake-content');

    $brand = Brand::factory()->create([
        'logo' => 'brands/old-logo.webp',
    ]);

    $action = resolve(UpdateBrandAction::class);

    $file = UploadedFile::fake()->image('logo.png', 800, 600);

    $updatedBrand = $action->handle($brand, [
        'logo' => $file,
    ]);

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

    $action = resolve(UpdateBrandAction::class);

    $updatedBrand = $action->handle($brand, [
        'is_active' => false,
    ]);

    expect($updatedBrand->is_active)->toBeFalse();
});

it('removes logo when set to null', function (): void {
    Storage::disk('public')->put('brands/old-logo.webp', 'fake-content');

    $brand = Brand::factory()->create([
        'logo' => 'brands/old-logo.webp',
    ]);

    expect(Storage::disk('public')->exists('brands/old-logo.webp'))->toBeTrue();

    $action = resolve(UpdateBrandAction::class);

    $updatedBrand = $action->handle($brand, [
        'logo' => null,
    ]);

    expect($updatedBrand->logo)->toBeNull()
        ->and(Storage::disk('public')->exists('brands/old-logo.webp'))->toBeFalse();
});
