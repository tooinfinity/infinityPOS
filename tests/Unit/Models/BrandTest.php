<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();

    $brand = Brand::factory()->create(['created_by' => $user->id])->refresh();

    expect(array_keys($brand->toArray()))
        ->toBe([
            'id',
            'name',
            'is_active',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});
