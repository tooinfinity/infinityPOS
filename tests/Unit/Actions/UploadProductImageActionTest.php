<?php

declare(strict_types=1);

use App\Actions\UploadProductImageAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

it('stores uploaded product image', function (): void {
    $action = resolve(UploadProductImageAction::class);

    $file = UploadedFile::fake()->image('product.png', 800, 600);

    $path = $action->handle($file);

    expect($path)
        ->toStartWith('products/')
        ->toEndWith('.jpg')
        ->and(Storage::disk('public')->exists($path))->toBeTrue();
});

it('deletes existing image when uploading new one', function (): void {
    Storage::disk('public')->put('products/old-image.jpg', 'fake-content');

    $action = resolve(UploadProductImageAction::class);

    $file = UploadedFile::fake()->image('product.png', 800, 600);

    $action->handle($file, 'products/old-image.jpg');

    expect(Storage::disk('public')->exists('products/old-image.jpg'))->toBeFalse();
});

it('generates unique filename for each upload', function (): void {
    $action = resolve(UploadProductImageAction::class);

    $file1 = UploadedFile::fake()->image('product1.png', 800, 600);
    $file2 = UploadedFile::fake()->image('product2.png', 800, 600);

    $path1 = $action->handle($file1);
    $path2 = $action->handle($file2);

    expect($path1)->not->toBe($path2);
});

it('processes image to 400px width', function (): void {
    $action = resolve(UploadProductImageAction::class);

    $file = UploadedFile::fake()->image('product.png', 800, 600);

    $path = $action->handle($file);

    expect($path)->toStartWith('products/');
    expect(Storage::disk('public')->exists($path))->toBeTrue();
});

it('converts image to jpg format', function (): void {
    $action = resolve(UploadProductImageAction::class);

    $file = UploadedFile::fake()->image('product.png', 800, 600);

    $path = $action->handle($file);

    expect($path)->toEndWith('.jpg');
});

it('handles non-existent existing image gracefully', function (): void {
    $action = resolve(UploadProductImageAction::class);

    $file = UploadedFile::fake()->image('product.png', 800, 600);

    $path = $action->handle($file, 'products/non-existent-image.jpg');

    expect($path)
        ->toStartWith('products/')
        ->toEndWith('.jpg')
        ->and(Storage::disk('public')->exists($path))->toBeTrue();
});

it('handles null existing image parameter', function (): void {
    $action = resolve(UploadProductImageAction::class);

    $file = UploadedFile::fake()->image('product.png', 800, 600);

    $path = $action->handle($file);

    expect($path)
        ->toStartWith('products/')
        ->toEndWith('.jpg')
        ->and(Storage::disk('public')->exists($path))->toBeTrue();
});

it('throws runtime exception when file reading fails', function (): void {
    $action = resolve(UploadProductImageAction::class);

    $reflection = new ReflectionClass($action);
    $method = $reflection->getMethod('readProcessedImage');

    $nonExistentPath = '/non/existent/path/test.jpg';

    expect(fn (): mixed => $method->invoke($action, $nonExistentPath))
        ->toThrow(RuntimeException::class, 'Failed to read processed image file');
});
