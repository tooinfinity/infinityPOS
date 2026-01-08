<?php

declare(strict_types=1);

use App\Collections\InventoryBatchCollection;
use App\Models\InventoryBatch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('to array', function (): void {
    $inventoryBatch = InventoryBatch::factory()->create()->refresh();

    expect(array_keys($inventoryBatch->toArray()))
        ->toBe([
            'id',
            'store_id',
            'product_id',
            'purchase_item_id',
            'quantity_received',
            'quantity_remaining',
            'unit_cost',
            'batch_date',
            'created_at',
            'updated_at',
        ]);
});

test('new collection returns inventory batch collection', function (): void {
    $inventoryBatch = new InventoryBatch();

    expect($inventoryBatch->newCollection([]))
        ->toBeInstanceOf(InventoryBatchCollection::class);
});

test('store relationship returns belongs to', function (): void {
    $inventoryBatch = new InventoryBatch();

    expect($inventoryBatch->store())
        ->toBeInstanceOf(BelongsTo::class);
});

test('product relationship returns belongs to', function (): void {
    $inventoryBatch = new InventoryBatch();

    expect($inventoryBatch->product())
        ->toBeInstanceOf(BelongsTo::class);
});

test('purchase item relationship returns belongs to', function (): void {
    $inventoryBatch = new InventoryBatch();

    expect($inventoryBatch->purchaseItem())
        ->toBeInstanceOf(BelongsTo::class);
});

test('sale item batches relationship returns has many', function (): void {
    $inventoryBatch = new InventoryBatch();

    expect($inventoryBatch->saleItemBatches())
        ->toBeInstanceOf(HasMany::class);
});

test('has remaining quantity returns true when quantity remaining is positive', function (): void {
    $inventoryBatch = InventoryBatch::factory()->make(['quantity_remaining' => 10]);

    expect($inventoryBatch->hasRemainingQuantity())->toBeTrue();
});

test('has remaining quantity returns false when quantity remaining is zero', function (): void {
    $inventoryBatch = InventoryBatch::factory()->make(['quantity_remaining' => 0]);

    expect($inventoryBatch->hasRemainingQuantity())->toBeFalse();
});

test('has remaining quantity returns false when quantity remaining is negative', function (): void {
    $inventoryBatch = InventoryBatch::factory()->make(['quantity_remaining' => -5]);

    expect($inventoryBatch->hasRemainingQuantity())->toBeFalse();
});

test('casts returns correct array', function (): void {
    $inventoryBatch = new InventoryBatch();

    expect($inventoryBatch->casts())
        ->toBe([
            'id' => 'integer',
            'store_id' => 'integer',
            'product_id' => 'integer',
            'purchase_item_id' => 'integer',
            'quantity_received' => 'integer',
            'quantity_remaining' => 'integer',
            'unit_cost' => 'integer',
            'batch_date' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $inventoryBatch = InventoryBatch::factory()->create()->refresh();

    expect($inventoryBatch->id)->toBeInt()
        ->and($inventoryBatch->store_id)->toBeInt()
        ->and($inventoryBatch->quantity_received)->toBeInt()
        ->and($inventoryBatch->unit_cost)->toBeInt()
        ->and($inventoryBatch->batch_date)->toBeInstanceOf(DateTimeInterface::class);
});
