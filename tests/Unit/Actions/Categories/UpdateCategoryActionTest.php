<?php

declare(strict_types=1);

use App\Actions\Categories\UpdateCategory;
use App\Data\Categories\UpdateCategoryData;
use App\Enums\CategoryTypeEnum;
use App\Models\Category;
use App\Models\User;

it('may update a category', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'name' => 'Old Category',
        'code' => 'OLD-001',
        'type' => CategoryTypeEnum::PRODUCT,
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $user2 = User::factory()->create();
    $action = resolve(UpdateCategory::class);

    $data = UpdateCategoryData::from([
        'name' => 'Updated Category',
        'code' => 'UPD-001',
        'type' => CategoryTypeEnum::EXPENSE,
        'is_active' => false,
        'updated_by' => $user2->id,
    ]);

    $action->handle($category, $data);

    expect($category->refresh()->name)->toBe('Updated Category')
        ->and($category->code)->toBe('UPD-001')
        ->and($category->type)->toBe(CategoryTypeEnum::EXPENSE)
        ->and($category->is_active)->toBeFalse()
        ->and($category->updated_by)->toBe($user2->id);
});
