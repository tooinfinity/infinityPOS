<?php

declare(strict_types=1);

use App\Actions\Categories\DeleteCategory;
use App\Models\Category;
use App\Models\User;

it('may delete a category', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['created_by' => $user->id]);

    $action = resolve(DeleteCategory::class);
    $action->handle($category);

    expect(Category::query()->find($category->id))->toBeNull()
        ->and($category->created_by)->toBeNull();
});
