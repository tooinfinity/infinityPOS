<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();

    $category = Category::factory()->create(['created_by' => $user->id])->refresh();

    expect(array_keys($category->toArray()))
        ->toBe([
            'id',
            'name',
            'code',
            'type',
            'is_active',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});
