<?php

declare(strict_types=1);

use App\Data\Brand\UpdateBrandData;
use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Optional;

it('may be created with optional fields', function (): void {
    $data = new UpdateBrandData(
        name: Optional::create(),
        slug: Optional::create(),
        logo: Optional::create(),
        is_active: Optional::create(),
    );

    expect($data->name)->toBeInstanceOf(Optional::class);
});

it('may be created with uploaded file logo', function (): void {
    $file = UploadedFile::fake()->image('brand.jpg');

    $data = new UpdateBrandData(
        name: Optional::create(),
        slug: Optional::create(),
        logo: $file,
        is_active: Optional::create(),
    );

    expect($data->logo)->toBeInstanceOf(UploadedFile::class);
});

it('may be created with null logo', function (): void {
    $data = new UpdateBrandData(
        name: Optional::create(),
        slug: Optional::create(),
        logo: null,
        is_active: Optional::create(),
    );

    expect($data->logo)->toBeNull();
});
