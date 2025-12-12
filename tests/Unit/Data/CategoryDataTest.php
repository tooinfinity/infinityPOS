<?php

declare(strict_types=1);

use App\Data\CategoryData;
use App\Data\UserData;
use App\Models\Category;
use App\Models\User;

it('transforms a category model into CategoryData', function (): void {

    $creator = User::factory()->create();
    $updater = User::factory()->create();

    /** @var Category $category */
    $category = Category::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->create([
            'name' => 'Food',
            'code' => 'F01',
            'type' => App\Enums\CategoryTypeEnum::PRODUCT->value,
            'is_active' => true,
        ]);

    $data = CategoryData::from(
        $category->load(['creator', 'updater'])
    );

    expect($data)
        ->toBeInstanceOf(CategoryData::class)
        ->id->toBe($category->id)
        ->name->toBe('Food')
        ->code->toBe('F01')
        ->type->toBe(App\Enums\CategoryTypeEnum::PRODUCT)
        ->is_active->toBeTrue()
        ->and($data->creator->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($creator->id)
        ->and($data->updater->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($updater->id)
        ->and($data->created_at)
        ->toBe($category->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($category->updated_at->toDateTimeString());

});
