<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\Tax;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create()->refresh();

    $tax = Tax::factory()->create(['created_by' => $user->id])->refresh();

    expect(array_keys($tax->toArray()))
        ->toBe([
            'id',
            'name',
            'tax_type',
            'rate',
            'is_active',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});

test('tax relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $tax = Tax::factory()->create(['created_by' => $user->id]);
    $tax->update(['updated_by' => $user->id]);

    $products = Product::factory()->create([
        'tax_id' => $tax->id,
        'created_by' => $user->id,
    ])->refresh();

    expect($tax->creator->id)->toBe($user->id)
        ->and($tax->updater->id)->toBe($user->id)
        ->and($tax->products->first()->id)->toBe($products->id)
        ->and($tax->products->first()->tax->id)->toBe($tax->id);
});
