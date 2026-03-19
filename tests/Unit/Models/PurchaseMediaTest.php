<?php

declare(strict_types=1);

use App\Enums\MediaCollection;
use App\Models\Purchase;
use Illuminate\Http\UploadedFile;

describe('Purchase Media Methods', function (): void {
    describe('registerMediaCollections', function (): void {
        it('can add media to attachment collection', function (): void {
            $purchase = Purchase::factory()->create();
            $file = UploadedFile::fake()->image('receipt.jpg');

            $media = $purchase->addMedia($file)->toMediaCollection(MediaCollection::PurchaseAttachment->value);

            expect($media)->not->toBeNull()
                ->and($purchase->getFirstMedia(MediaCollection::PurchaseAttachment->value))->not->toBeNull();
        });

        it('attachment collection allows images', function (): void {
            $purchase = Purchase::factory()->create();
            $file = UploadedFile::fake()->image('receipt.jpg');

            $media = $purchase->addMedia($file)->toMediaCollection(MediaCollection::PurchaseAttachment->value);

            expect($media->mime_type)->toBe('image/jpeg');
        });

        it('attachment collection replaces existing file', function (): void {
            $purchase = Purchase::factory()->create();
            $file1 = UploadedFile::fake()->image('doc1.jpg');
            $file2 = UploadedFile::fake()->image('doc2.jpg');

            $purchase->addMedia($file1)->toMediaCollection(MediaCollection::PurchaseAttachment->value);
            $purchase->addMedia($file2)->toMediaCollection(MediaCollection::PurchaseAttachment->value);

            expect($purchase->getMedia(MediaCollection::PurchaseAttachment->value))->toHaveCount(1);
        });
    });

    describe('attachment attribute', function (): void {
        it('returns null when no attachment', function (): void {
            $purchase = Purchase::factory()->create();

            expect($purchase->attachment)->toBeNull();
        });

        it('returns attachment data array when attachment exists', function (): void {
            $purchase = Purchase::factory()->create();
            $file = UploadedFile::fake()->image('receipt.jpg');

            $media = $purchase->addMedia($file)->toMediaCollection(MediaCollection::PurchaseAttachment->value);

            expect($purchase->attachment)->toBeArray()
                ->and($purchase->attachment)->toHaveKey('id')
                ->and($purchase->attachment)->toHaveKey('name')
                ->and($purchase->attachment)->toHaveKey('url')
                ->and($purchase->attachment)->toHaveKey('size')
                ->and($purchase->attachment)->toHaveKey('mime')
                ->and($purchase->attachment)->toHaveKey('extension')
                ->and($purchase->attachment)->toHaveKey('is_image')
                ->and($purchase->attachment['id'])->toBe($media->id)
                ->and($purchase->attachment['is_image'])->toBeTrue();
        });
    });
});
