<?php

declare(strict_types=1);

use App\Actions\Media\DeleteMedia;
use App\Actions\Media\ReplaceMedia;
use App\Actions\Media\UploadMedia;
use App\Data\UploadMediaData;
use App\Enums\MediaCollection;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

describe(DeleteMedia::class, function (): void {
    it('may delete media', function (): void {
        $brand = Brand::factory()->create();

        $file = UploadedFile::fake()->image('logo.jpg');
        $media = $brand->addMedia($file)->toMediaCollection(MediaCollection::BrandLogo->value);

        $action = resolve(DeleteMedia::class);

        $action->handle($media);

        expect($media->fresh())->toBeNull();
    });

    it('deletes media permanently', function (): void {
        $brand = Brand::factory()->create();

        $file = UploadedFile::fake()->image('logo.jpg');
        $media = $brand->addMedia($file)->toMediaCollection(MediaCollection::BrandLogo->value);

        $action = resolve(DeleteMedia::class);

        $action->handle($media);

        expect(Media::query()->where('id', $media->id)->exists())->toBeFalse();
    });
});

describe(UploadMedia::class, function (): void {
    beforeEach(function (): void {
        $this->brand = Brand::factory()->create();
    });

    it('may upload media to a model', function (): void {
        $file = UploadedFile::fake()->image('logo.jpg');
        $data = new UploadMediaData(
            file: $file,
            collection: MediaCollection::BrandLogo,
            name: 'Test Logo',
            customProperties: [],
        );

        $action = resolve(UploadMedia::class);

        $media = $action->handle($this->brand, $data);

        expect($media)->toBeInstanceOf(Media::class)
            ->and($media->name)->toBe('Test Logo')
            ->and($media->collection_name)->toBe(MediaCollection::BrandLogo->value)
            ->and($media->file_name)->toStartWith('logo')
            ->and($media->mime_type)->toBe('image/jpeg');
    });

    it('uploads media without custom name', function (): void {
        $file = UploadedFile::fake()->image('brand-logo.png');
        $data = new UploadMediaData(
            file: $file,
            collection: MediaCollection::BrandLogo,
            name: null,
            customProperties: [],
        );

        $action = resolve(UploadMedia::class);

        $media = $action->handle($this->brand, $data);

        expect($media)->toBeInstanceOf(Media::class)
            ->and($media->collection_name)->toBe(MediaCollection::BrandLogo->value)
            ->and($media->mime_type)->toBe('image/png');
    });

    it('uploads media with custom properties', function (): void {
        $file = UploadedFile::fake()->image('logo.jpg');
        $data = new UploadMediaData(
            file: $file,
            collection: MediaCollection::BrandLogo,
            name: 'Logo',
            customProperties: ['alt' => 'Brand Logo', 'category' => 'branding'],
        );

        $action = resolve(UploadMedia::class);

        $media = $action->handle($this->brand, $data);

        expect($media->custom_properties)->toBe([
            'alt' => 'Brand Logo',
            'category' => 'branding',
        ]);
    });

    it('throws exception when storage is not writable', function (): void {
        $storagePath = storage_path('app');
        $originalPermissions = fileperms($storagePath);

        chmod($storagePath, 0444);

        $file = UploadedFile::fake()->image('logo.jpg');
        $data = new UploadMediaData(
            file: $file,
            collection: MediaCollection::BrandLogo,
            name: 'Logo',
            customProperties: [],
        );

        $action = resolve(UploadMedia::class);

        expect(fn () => $action->handle($this->brand, $data))
            ->toThrow(App\Exceptions\MediaUploadException::class);

        chmod($storagePath, $originalPermissions);
    });
});

describe(ReplaceMedia::class, function (): void {
    beforeEach(function (): void {
        $this->brand = Brand::factory()->create();
    });

    it('may replace existing media', function (): void {
        $originalFile = UploadedFile::fake()->image('original.jpg');
        $originalMedia = $this->brand->addMedia($originalFile)->toMediaCollection(MediaCollection::BrandLogo->value);

        $newFile = UploadedFile::fake()->image('replacement.png');
        $data = new UploadMediaData(
            file: $newFile,
            collection: MediaCollection::BrandLogo,
            name: 'Replacement Logo',
            customProperties: [],
        );

        $action = resolve(ReplaceMedia::class);

        $newMedia = $action->handle($this->brand, $data);

        expect($newMedia)->toBeInstanceOf(Media::class)
            ->and($newMedia->id)->not->toBe($originalMedia->id)
            ->and($newMedia->mime_type)->toBe('image/png')
            ->and($this->brand->getMedia(MediaCollection::BrandLogo->value))->toHaveCount(1);
    });

    it('clears media collection before uploading new media', function (): void {
        $originalFile = UploadedFile::fake()->image('original.jpg');
        $this->brand->addMedia($originalFile)->toMediaCollection(MediaCollection::BrandLogo->value);

        $newFile = UploadedFile::fake()->image('replacement.png');
        $data = new UploadMediaData(
            file: $newFile,
            collection: MediaCollection::BrandLogo,
            name: 'Replacement',
            customProperties: ['replaced' => true],
        );

        $action = resolve(ReplaceMedia::class);

        $action->handle($this->brand, $data);

        expect($this->brand->getMedia(MediaCollection::BrandLogo->value))->toHaveCount(1)
            ->and($this->brand->getFirstMedia(MediaCollection::BrandLogo->value)->mime_type)
            ->toBe('image/png');
    });

    it('replaces media with new custom properties', function (): void {
        $originalFile = UploadedFile::fake()->image('original.jpg');
        $this->brand->addMedia($originalFile)->toMediaCollection(MediaCollection::BrandLogo->value);

        $newFile = UploadedFile::fake()->image('new.jpg');
        $data = new UploadMediaData(
            file: $newFile,
            collection: MediaCollection::BrandLogo,
            name: 'New Logo',
            customProperties: ['version' => '2.0'],
        );

        $action = resolve(ReplaceMedia::class);

        $newMedia = $action->handle($this->brand, $data);

        expect($newMedia->custom_properties)->toBe(['version' => '2.0']);
    });
});
