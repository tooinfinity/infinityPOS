<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Product;
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

test('brand relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $brand = Brand::factory()->create(['created_by' => $user->id]);
    $product = Product::factory()->create(['brand_id' => $brand->id, 'created_by' => $user->id]);

    $brand->update(['updated_by' => $user->id]);

    expect($brand->products)->toHaveCount(1)
        ->and($brand->creator->id)->toBe($user->id)
        ->and($brand->updater->id)->toBe($user->id);
});
