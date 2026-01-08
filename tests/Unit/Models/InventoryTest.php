<?php

declare(strict_types=1);

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

test('to array', function (): void {
    $inventory = Inventory::factory()->create()->refresh();

    expect(array_keys($inventory->toArray()))
        ->toBe([
            'id',
            'store_id',
            'product_id',
            'total_quantity',
            'updated_at',
        ]);
});

test('store relationship returns belongs to', function (): void {
    $inventory = new Inventory();

    expect($inventory->store())
        ->toBeInstanceOf(BelongsTo::class);
});

test('product relationship returns belongs to', function (): void {
    $inventory = new Inventory();

    expect($inventory->product())
        ->toBeInstanceOf(BelongsTo::class);
});

test('casts returns correct array', function (): void {
    $inventory = new Inventory();

    expect($inventory->casts())
        ->toBe([
            'id' => 'integer',
            'store_id' => 'integer',
            'product_id' => 'integer',
            'total_quantity' => 'integer',
            'updated_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $inventory = Inventory::factory()->create()->refresh();

    expect($inventory->id)->toBeInt()
        ->and($inventory->store_id)->toBeInt()
        ->and($inventory->product_id)->toBeInt()
        ->and($inventory->total_quantity)->toBeInt()
        ->and($inventory->updated_at)->toBeInstanceOf(DateTimeInterface::class);
});
