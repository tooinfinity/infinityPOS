<?php

declare(strict_types=1);

use App\Data\Brand\CreateBrandData;
use Illuminate\Http\UploadedFile;

it('may be created with required fields', function (): void {
    $data = new CreateBrandData(
        name: 'Test Brand',
        slug: null,
        logo: null,
        is_active: true,
    );

    expect($data)
        ->name->toBe('Test Brand')
        ->slug->toBeNull()
        ->logo->toBeNull()
        ->is_active->toBeTrue();
});

it('may be created with uploaded file logo', function (): void {
    $file = UploadedFile::fake()->image('brand.jpg');

    $data = new CreateBrandData(
        name: 'Test Brand',
        slug: null,
        logo: $file,
        is_active: true,
    );

    expect($data->logo)->toBeInstanceOf(UploadedFile::class);
});

it('may be created with string logo path', function (): void {
    $data = new CreateBrandData(
        name: 'Test Brand',
        slug: null,
        logo: 'brands/logo.jpg',
        is_active: true,
    );

    expect($data->logo)->toBe('brands/logo.jpg');
});
