<?php

declare(strict_types=1);

use App\Data\Category\UpdateCategoryData;
use Spatie\LaravelData\Optional;

it('may be created with optional fields', function (): void {
    $data = new UpdateCategoryData(
        name: Optional::create(),
        slug: Optional::create(),
        description: Optional::create(),
        is_active: Optional::create(),
    );

    expect($data->name)->toBeInstanceOf(Optional::class);
});

it('may be created with specific values', function (): void {
    $data = new UpdateCategoryData(
        name: 'Updated Category',
        slug: 'updated-slug',
        description: Optional::create(),
        is_active: false,
    );

    expect($data->name)->toBe('Updated Category')
        ->and($data->slug)->toBe('updated-slug')
        ->and($data->is_active)->toBeFalse();
});
