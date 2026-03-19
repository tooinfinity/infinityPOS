<?php

declare(strict_types=1);

use App\Enums\MediaCollection;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;

describe('Brand Media Methods', function (): void {
    describe('registerMediaCollections', function (): void {
        it('can add media to logo collection', function (): void {
            $brand = Brand::factory()->create();
            $file = UploadedFile::fake()->image('logo.jpg');

            $media = $brand->addMedia($file)->toMediaCollection(MediaCollection::BrandLogo->value);

            expect($media)->not->toBeNull()
                ->and($brand->getFirstMedia(MediaCollection::BrandLogo->value))->not->toBeNull();
        });

        it('logo collection only allows images', function (): void {
            $brand = Brand::factory()->create();
            $file = UploadedFile::fake()->image('logo.jpg');

            $media = $brand->addMedia($file)->toMediaCollection(MediaCollection::BrandLogo->value);

            expect($media->mime_type)->toBe('image/jpeg');
        });

        it('logo collection replaces existing file', function (): void {
            $brand = Brand::factory()->create();
            $file1 = UploadedFile::fake()->image('logo1.jpg');
            $file2 = UploadedFile::fake()->image('logo2.jpg');

            $brand->addMedia($file1)->toMediaCollection(MediaCollection::BrandLogo->value);
            $brand->addMedia($file2)->toMediaCollection(MediaCollection::BrandLogo->value);

            expect($brand->getMedia(MediaCollection::BrandLogo->value))->toHaveCount(1);
        });
    });

    describe('registerMediaConversions', function (): void {
        it('creates thumb conversion', function (): void {
            $brand = Brand::factory()->create();
            $file = UploadedFile::fake()->image('logo.jpg');

            $brand->addMedia($file)->toMediaCollection(MediaCollection::BrandLogo->value);

            $media = $brand->getFirstMedia(MediaCollection::BrandLogo->value);
            expect($media->getUrl('thumb'))->toBeString();
        });
    });

    describe('logo attribute', function (): void {
        it('returns null when no logo', function (): void {
            $brand = Brand::factory()->create();

            expect($brand->logo)->toBeNull();
        });

        it('returns logo data array when logo exists', function (): void {
            $brand = Brand::factory()->create();
            $file = UploadedFile::fake()->image('logo.jpg');

            $media = $brand->addMedia($file)->toMediaCollection(MediaCollection::BrandLogo->value);

            expect($brand->logo)->toBeArray()
                ->and($brand->logo)->toHaveKey('id')
                ->and($brand->logo)->toHaveKey('url')
                ->and($brand->logo)->toHaveKey('thumb')
                ->and($brand->logo)->toHaveKey('size')
                ->and($brand->logo['id'])->toBe($media->id);
        });
    });
});
