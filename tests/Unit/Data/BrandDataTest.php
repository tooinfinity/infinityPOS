<?php

declare(strict_types=1);

use App\Data\Brands\BrandData;
use App\Data\Users\UserData;
use App\Models\Brand;
use App\Models\User;

it('transforms a brand model into BrandData', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    /** @var Brand $brand */
    $brand = Brand::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->create();

    $data = BrandData::from(
        $brand->load(['creator', 'updater'])
    );

    expect($data)
        ->toBeInstanceOf(BrandData::class)
        ->id->toBe($brand->id)
        ->name->toBe($brand->name)
        ->is_active->toBe($brand->is_active)
        ->and($data->creator->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($creator->id)
        ->and($data->updater->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($updater->id)
        ->and($data->created_at)
        ->toBe($brand->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($brand->updated_at->toDateTimeString());

});
