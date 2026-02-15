<?php

declare(strict_types=1);

use App\Actions\UploadImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

it('stores uploaded image in products directory', function (): void {
    $action = resolve(UploadImage::class);

    $file = UploadedFile::fake()->image('product.png', 800, 600);

    $path = $action->handle($file, 'products');

    expect($path)
        ->toStartWith('products/')
        ->toEndWith('.webp')
        ->and(Storage::disk('public')->exists($path))->toBeTrue();
});

it('stores uploaded image in brands directory', function (): void {
    $action = resolve(UploadImage::class);

    $file = UploadedFile::fake()->image('logo.jpg', 800, 600);

    $path = $action->handle($file, 'brands');

    expect($path)
        ->toStartWith('brands/')
        ->toEndWith('.webp')
        ->and(Storage::disk('public')->exists($path))->toBeTrue();
});

it('converts png to webp format', function (): void {
    $action = resolve(UploadImage::class);

    $file = UploadedFile::fake()->image('image.png', 800, 600);

    $path = $action->handle($file, 'uploads');

    expect($path)->toEndWith('.webp');
});

it('converts jpg to webp format', function (): void {
    $action = resolve(UploadImage::class);

    $file = UploadedFile::fake()->image('image.jpg', 800, 600);

    $path = $action->handle($file, 'uploads');

    expect($path)->toEndWith('.webp');
});

it('converts jpeg to webp format', function (): void {
    $action = resolve(UploadImage::class);

    $file = UploadedFile::fake()->image('image.jpeg', 800, 600);

    $path = $action->handle($file, 'uploads');

    expect($path)->toEndWith('.webp');
});

it('keeps webp as webp format', function (): void {
    $action = resolve(UploadImage::class);

    $file = UploadedFile::fake()->image('image.webp', 800, 600);

    $path = $action->handle($file, 'uploads');

    expect($path)->toEndWith('.webp');
});

it('rejects gif format', function (): void {
    $action = resolve(UploadImage::class);

    $file = UploadedFile::fake()->image('image.gif', 800, 600);

    expect(fn (): string => $action->handle($file, 'uploads'))
        ->toThrow(InvalidArgumentException::class, 'Invalid image format');
});

it('rejects svg format', function (): void {
    $action = resolve(UploadImage::class);

    $file = UploadedFile::fake()->create('image.svg', 100, 'image/svg+xml');

    expect(fn (): string => $action->handle($file, 'uploads'))
        ->toThrow(InvalidArgumentException::class, 'Invalid image format');
});

it('rejects files larger than 2MB', function (): void {
    $action = resolve(UploadImage::class);

    $file = UploadedFile::fake()->image('image.png', 800, 600)->size(2049);

    expect(fn (): string => $action->handle($file, 'uploads'))
        ->toThrow(InvalidArgumentException::class, 'Image size exceeds maximum allowed size');
});

it('accepts files up to 2MB', function (): void {
    $action = resolve(UploadImage::class);

    $file = UploadedFile::fake()->image('image.png', 800, 600)->size(2048);

    $path = $action->handle($file, 'uploads');

    expect($path)->toStartWith('uploads/');
});

it('deletes existing image when uploading new one', function (): void {
    Storage::disk('public')->put('products/old-image.jpg', 'fake-content');

    $action = resolve(UploadImage::class);

    $file = UploadedFile::fake()->image('product.png', 800, 600);

    $action->handle($file, 'products', 'products/old-image.jpg');

    expect(Storage::disk('public')->exists('products/old-image.jpg'))->toBeFalse();
});

it('deletes existing logo when uploading new one', function (): void {
    Storage::disk('public')->put('brands/old-logo.png', 'fake-content');

    $action = resolve(UploadImage::class);

    $file = UploadedFile::fake()->image('logo.jpg', 800, 600);

    $action->handle($file, 'brands', 'brands/old-logo.png');

    expect(Storage::disk('public')->exists('brands/old-logo.png'))->toBeFalse();
});

it('generates unique filename for each upload', function (): void {
    $action = resolve(UploadImage::class);

    $file1 = UploadedFile::fake()->image('image1.png', 800, 600);
    $file2 = UploadedFile::fake()->image('image2.png', 800, 600);

    $path1 = $action->handle($file1, 'uploads');
    $path2 = $action->handle($file2, 'uploads');

    expect($path1)->not->toBe($path2);
});

it('processes image to default 400px width', function (): void {
    $action = resolve(UploadImage::class);

    $file = UploadedFile::fake()->image('image.png', 800, 600);

    $path = $action->handle($file, 'uploads');

    expect($path)->toStartWith('uploads/');
    expect(Storage::disk('public')->exists($path))->toBeTrue();
});

it('processes image with custom max width', function (): void {
    $action = resolve(UploadImage::class);

    $file = UploadedFile::fake()->image('image.png', 1200, 900);

    $path = $action->handle($file, 'uploads', null, 800);

    expect($path)
        ->toStartWith('uploads/')
        ->toEndWith('.webp')
        ->and(Storage::disk('public')->exists($path))->toBeTrue();
});

it('converts all allowed formats to webp', function (string $extension): void {
    $action = resolve(UploadImage::class);

    $file = UploadedFile::fake()->image("image.{$extension}", 800, 600);

    $path = $action->handle($file, 'uploads');

    expect($path)->toEndWith('.webp');
})->with(['png', 'jpg', 'jpeg', 'webp']);

it('handles non-existent existing image gracefully', function (): void {
    $action = resolve(UploadImage::class);

    $file = UploadedFile::fake()->image('image.png', 800, 600);

    $path = $action->handle($file, 'uploads', 'uploads/non-existent-image.webp');

    expect($path)
        ->toStartWith('uploads/')
        ->toEndWith('.webp')
        ->and(Storage::disk('public')->exists($path))->toBeTrue();
});

it('handles null existing image parameter', function (): void {
    $action = resolve(UploadImage::class);

    $file = UploadedFile::fake()->image('image.png', 800, 600);

    $path = $action->handle($file, 'uploads');

    expect($path)
        ->toStartWith('uploads/')
        ->toEndWith('.webp')
        ->and(Storage::disk('public')->exists($path))->toBeTrue();
});

it('handles null max width by using default', function (): void {
    $action = resolve(UploadImage::class);

    $file = UploadedFile::fake()->image('image.png', 800, 600);

    $path = $action->handle($file, 'uploads');

    expect($path)
        ->toStartWith('uploads/')
        ->toEndWith('.webp')
        ->and(Storage::disk('public')->exists($path))->toBeTrue();
});
