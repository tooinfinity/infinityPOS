<?php

declare(strict_types=1);

use App\Data\UploadMediaData;
use App\Enums\MediaCollection;
use App\Http\Requests\UploadBrandLogoRequest;
use App\Http\Requests\UploadProductMediaRequest;
use App\Http\Requests\UploadPurchaseAttachmentRequest;
use Illuminate\Http\UploadedFile;

describe(UploadMediaData::class, function (): void {
    it('may create upload media data', function (): void {
        $file = UploadedFile::fake()->image('logo.jpg');
        $collection = MediaCollection::BrandLogo;

        $data = new UploadMediaData(
            file: $file,
            collection: $collection,
        );

        expect($data)->toBeInstanceOf(UploadMediaData::class)
            ->and($data->file)->toBe($file)
            ->and($data->collection)->toBe($collection)
            ->and($data->name)->toBeNull()
            ->and($data->customProperties)->toBe([]);
    });

    it('creates with custom name', function (): void {
        $file = UploadedFile::fake()->image('logo.jpg');

        $data = new UploadMediaData(
            file: $file,
            collection: MediaCollection::BrandLogo,
            name: 'Custom Logo',
        );

        expect($data->name)->toBe('Custom Logo');
    });

    it('creates with custom properties', function (): void {
        $file = UploadedFile::fake()->image('logo.jpg');

        $data = new UploadMediaData(
            file: $file,
            collection: MediaCollection::BrandLogo,
            customProperties: ['alt' => 'Brand Logo', 'category' => 'branding'],
        );

        expect($data->customProperties)->toBe([
            'alt' => 'Brand Logo',
            'category' => 'branding',
        ]);
    });

    it('creates with all parameters', function (): void {
        $file = UploadedFile::fake()->image('logo.jpg');

        $data = new UploadMediaData(
            file: $file,
            collection: MediaCollection::BrandLogo,
            name: 'Logo',
            customProperties: ['version' => '1.0'],
        );

        expect($data->file)->toBe($file)
            ->and($data->collection)->toBe(MediaCollection::BrandLogo)
            ->and($data->name)->toBe('Logo')
            ->and($data->customProperties)->toBe(['version' => '1.0']);
    });

    it('has readonly properties', function (): void {
        $file = UploadedFile::fake()->image('logo.jpg');

        $data = new UploadMediaData(
            file: $file,
            collection: MediaCollection::BrandLogo,
        );

        expect(fn () => $data->file = UploadedFile::fake()->image('other.jpg'))
            ->toThrow(Error::class);
    });

    describe('static factory methods', function (): void {
        it('creates from brand logo request', function (): void {
            $file = UploadedFile::fake()->image('logo.jpg');
            $request = new UploadBrandLogoRequest;
            $request->files->set('logo', $file);
            $request->setMethod('POST');
            $request->initialize(['logo' => $file], ['logo' => $file], [], [], ['logo' => $file], [], []);

            $data = UploadMediaData::forBrandLogo($request);

            expect($data)->toBeInstanceOf(UploadMediaData::class)
                ->and($data->file)->toBe($file)
                ->and($data->collection)->toBe(MediaCollection::BrandLogo)
                ->and($data->name)->toBeNull()
                ->and($data->customProperties)->toBe([]);
        });

        it('creates from product thumbnail request', function (): void {
            $file = UploadedFile::fake()->image('thumbnail.jpg');
            $request = new UploadProductMediaRequest;
            $request->files->set('file', $file);
            $request->setMethod('POST');
            $request->initialize(['file' => $file], ['file' => $file], [], [], ['file' => $file], [], []);

            $data = UploadMediaData::forProductThumbnail($request);

            expect($data)->toBeInstanceOf(UploadMediaData::class)
                ->and($data->file)->toBe($file)
                ->and($data->collection)->toBe(MediaCollection::ProductThumbnail)
                ->and($data->name)->toBeNull()
                ->and($data->customProperties)->toBe([]);
        });

        it('creates from purchase attachment request', function (): void {
            $file = UploadedFile::fake()->create('invoice.pdf', 100, 'application/pdf');
            $request = new UploadPurchaseAttachmentRequest;
            $request->files->set('file', $file);
            $request->setMethod('POST');
            $request->initialize(['file' => $file], ['file' => $file], [], [], ['file' => $file], [], []);

            $data = UploadMediaData::forPurchaseAttachment($request);

            expect($data)->toBeInstanceOf(UploadMediaData::class)
                ->and($data->file)->toBe($file)
                ->and($data->collection)->toBe(MediaCollection::PurchaseAttachment)
                ->and($data->name)->toBe('invoice.pdf')
                ->and($data->customProperties)->toBe([]);
        });
    });
});

afterEach(function (): void {
    Mockery::close();
});
