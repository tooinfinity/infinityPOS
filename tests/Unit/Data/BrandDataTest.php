<?php

declare(strict_types=1);

use App\Data\BrandData;
use App\Data\ProductData;
use App\Data\UserData;
use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\LaravelData\DataCollection;

it('transforms a brand model into BrandData', function (): void {

    $creator = User::factory()->create();
    $updater = User::factory()->create();

    /** @var Brand $brand */
    $brand = Brand::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->has(Product::factory()->count(2), 'products')
        ->create();

    $data = BrandData::fromModel(
        $brand->load(['creator', 'updater', 'products'])
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
        ->id->toBe($updater->id);

    $products = $data->products->resolve();

    if ($products instanceof DataCollection) {
        expect($products)->toBeInstanceOf(DataCollection::class)
            ->and($products->count())->toBe(2);

        foreach ($products->all() as $p) {
            expect($p)->toBeInstanceOf(ProductData::class);
        }
    } else {
        expect($products)->toBeInstanceOf(Collection::class)
            ->and($products->count())->toBe(2);

        foreach ($products as $p) {
            expect($p)->toBeInstanceOf(ProductData::class);
        }
    }

    expect($data->created_at->toDateTimeString())
        ->toBe($brand->created_at->toDateTimeString())
        ->and($data->updated_at->toDateTimeString())
        ->toBe($brand->updated_at->toDateTimeString());

});
