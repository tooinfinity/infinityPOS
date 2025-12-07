<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\Unit;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();

    $unit = Unit::factory()->create(['created_by' => $user->id])->refresh();

    expect(array_keys($unit->toArray()))
        ->toBe([
            'id',
            'name',
            'short_name',
            'is_active',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});

test('unit relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $unit = Unit::factory()->create(['created_by' => $user->id]);
    $unit->update(['updated_by' => $user->id]);

    $product = Product::factory()->create([
        'unit_id' => $unit->id,
        'created_by' => $user->id,
    ])->refresh();

    expect($unit->creator->id)->toBe($user->id)
        ->and($unit->updater->id)->toBe($user->id)
        ->and($unit->products->first()->id)->toBe($product->id)
        ->and($unit->products->first()->unit->id)->toBe($unit->id);
});
