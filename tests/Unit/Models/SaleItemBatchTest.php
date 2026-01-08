<?php

declare(strict_types=1);

use App\Models\SaleItemBatch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

test('to array', function (): void {
    $saleItemBatch = SaleItemBatch::factory()->create()->refresh();

    expect(array_keys($saleItemBatch->toArray()))
        ->toBe([
            'id',
            'sale_item_id',
            'inventory_batch_id',
            'quantity_used',
            'unit_cost',
            'created_at',
        ]);
});

test('sale item relationship returns belongs to', function (): void {
    $saleItemBatch = new SaleItemBatch();

    expect($saleItemBatch->saleItem())
        ->toBeInstanceOf(BelongsTo::class);
});

test('inventory batch relationship returns belongs to', function (): void {
    $saleItemBatch = new SaleItemBatch();

    expect($saleItemBatch->inventoryBatch())
        ->toBeInstanceOf(BelongsTo::class);
});

test('casts returns correct array', function (): void {
    $saleItemBatch = new SaleItemBatch();

    expect($saleItemBatch->casts())
        ->toBe([
            'id' => 'integer',
            'sale_item_id' => 'integer',
            'inventory_batch_id' => 'integer',
            'quantity_used' => 'integer',
            'unit_cost' => 'integer',
            'created_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $saleItemBatch = SaleItemBatch::factory()->create()->refresh();

    expect($saleItemBatch->id)->toBeInt()
        ->and($saleItemBatch->quantity_used)->toBeInt()
        ->and($saleItemBatch->unit_cost)->toBeInt()
        ->and($saleItemBatch->created_at)->toBeInstanceOf(DateTimeInterface::class);
});
