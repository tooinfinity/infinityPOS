<?php

declare(strict_types=1);

use App\Enums\MediaCollection;
use App\Models\Product;
use Illuminate\Http\UploadedFile;

describe('Product Media Methods', function (): void {
    describe('registerMediaCollections', function (): void {
        it('can add media to thumbnail collection', function (): void {
            $product = Product::factory()->create();
            $file = UploadedFile::fake()->image('thumbnail.jpg');

            $media = $product->addMedia($file)->toMediaCollection(MediaCollection::ProductThumbnail->value);

            expect($media)->not->toBeNull()
                ->and($product->getFirstMedia(MediaCollection::ProductThumbnail->value))->not->toBeNull();
        });

        it('thumbnail collection only allows images', function (): void {
            $product = Product::factory()->create();
            $file = UploadedFile::fake()->image('thumbnail.jpg');

            $media = $product->addMedia($file)->toMediaCollection(MediaCollection::ProductThumbnail->value);

            expect($media->mime_type)->toBe('image/jpeg');
        });

        it('thumbnail collection replaces existing file', function (): void {
            $product = Product::factory()->create();
            $file1 = UploadedFile::fake()->image('thumb1.jpg');
            $file2 = UploadedFile::fake()->image('thumb2.jpg');

            $product->addMedia($file1)->toMediaCollection(MediaCollection::ProductThumbnail->value);
            $product->addMedia($file2)->toMediaCollection(MediaCollection::ProductThumbnail->value);

            expect($product->getMedia(MediaCollection::ProductThumbnail->value))->toHaveCount(1);
        });
    });

    describe('registerMediaConversions', function (): void {
        it('creates thumb conversion', function (): void {
            $product = Product::factory()->create();
            $file = UploadedFile::fake()->image('thumbnail.jpg');

            $product->addMedia($file)->toMediaCollection(MediaCollection::ProductThumbnail->value);

            $media = $product->getFirstMedia(MediaCollection::ProductThumbnail->value);
            expect($media->getUrl('thumb'))->toBeString();
        });
    });

    describe('thumbnail attribute', function (): void {
        it('returns null when no thumbnail', function (): void {
            $product = Product::factory()->create();

            expect($product->thumbnail)->toBeNull();
        });

        it('returns thumbnail data array when thumbnail exists', function (): void {
            $product = Product::factory()->create();
            $file = UploadedFile::fake()->image('thumbnail.jpg');

            $media = $product->addMedia($file)->toMediaCollection(MediaCollection::ProductThumbnail->value);

            expect($product->thumbnail)->toBeArray()
                ->and($product->thumbnail)->toHaveKey('id')
                ->and($product->thumbnail)->toHaveKey('url')
                ->and($product->thumbnail)->toHaveKey('thumb')
                ->and($product->thumbnail)->toHaveKey('size')
                ->and($product->thumbnail['id'])->toBe($media->id);
        });
    });
});
