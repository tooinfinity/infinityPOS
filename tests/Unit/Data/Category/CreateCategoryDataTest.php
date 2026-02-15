<?php

declare(strict_types=1);

use App\Data\Category\CreateCategoryData;

it('may be created with required fields', function (): void {
    $data = new CreateCategoryData(
        name: 'Test Category',
        slug: null,
        description: null,
        is_active: true,
    );

    expect($data)
        ->name->toBe('Test Category')
        ->slug->toBeNull()
        ->description->toBeNull()
        ->is_active->toBeTrue();
});

it('may be created with custom slug', function (): void {
    $data = new CreateCategoryData(
        name: 'Test Category',
        slug: 'custom-slug',
        description: null,
        is_active: true,
    );

    expect($data->slug)->toBe('custom-slug');
});

it('may be created with description', function (): void {
    $data = new CreateCategoryData(
        name: 'Test Category',
        slug: null,
        description: 'Test description',
        is_active: true,
    );

    expect($data->description)->toBe('Test description');
});
