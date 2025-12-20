<?php

declare(strict_types=1);

use App\Actions\Categories\CreateCategory;
use App\Data\Categories\CreateCategoryData;
use App\Enums\CategoryTypeEnum;
use App\Models\Category;
use App\Models\User;

it('may create a category', function (): void {
    $user = User::factory()->create();
    $action = resolve(CreateCategory::class);

    $data = CreateCategoryData::from([
        'name' => 'Test Category',
        'code' => 'TEST-001',
        'type' => CategoryTypeEnum::PRODUCT,
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $category = $action->handle($data);

    expect($category)->toBeInstanceOf(Category::class)
        ->and($category->name)->toBe('Test Category')
        ->and($category->code)->toBe('TEST-001')
        ->and($category->type)->toBe(CategoryTypeEnum::PRODUCT)
        ->and($category->is_active)->toBeTrue()
        ->and($category->created_by)->toBe($user->id);
});
