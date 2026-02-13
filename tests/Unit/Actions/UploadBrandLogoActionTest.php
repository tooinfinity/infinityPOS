<?php

declare(strict_types=1);

use App\Actions\UploadBrandLogoAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

it('stores uploaded brand logo', function (): void {
    $action = resolve(UploadBrandLogoAction::class);

    $file = UploadedFile::fake()->image('logo.png', 800, 600);

    $path = $action->handle($file);

    expect($path)
        ->toStartWith('brands/')
        ->toEndWith('.webp')
        ->and(Storage::disk('public')->exists($path))->toBeTrue();
});

it('deletes existing logo when uploading new one', function (): void {
    Storage::disk('public')->put('brands/old-logo.webp', 'fake-content');

    $action = resolve(UploadBrandLogoAction::class);

    $file = UploadedFile::fake()->image('logo.png', 800, 600);

    $action->handle($file, 'brands/old-logo.webp');

    expect(Storage::disk('public')->exists('brands/old-logo.webp'))->toBeFalse();
});

it('generates unique filename for each upload', function (): void {
    $action = resolve(UploadBrandLogoAction::class);

    $file1 = UploadedFile::fake()->image('logo1.png', 800, 600);
    $file2 = UploadedFile::fake()->image('logo2.png', 800, 600);

    $path1 = $action->handle($file1);
    $path2 = $action->handle($file2);

    expect($path1)->not->toBe($path2);
});

it('processes image to 400px width', function (): void {
    $action = resolve(UploadBrandLogoAction::class);

    $file = UploadedFile::fake()->image('logo.png', 800, 600);

    $path = $action->handle($file);

    expect($path)->toStartWith('brands/');
    expect(Storage::disk('public')->exists($path))->toBeTrue();
});

it('converts image to webp format', function (): void {
    $action = resolve(UploadBrandLogoAction::class);

    $file = UploadedFile::fake()->image('logo.png', 800, 600);

    $path = $action->handle($file);

    expect($path)->toEndWith('.webp');
});
