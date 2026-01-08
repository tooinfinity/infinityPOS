<?php

declare(strict_types=1);

use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

test('to array', function (): void {
    $purchaseItem = PurchaseItem::factory()->create()->refresh();

    expect(array_keys($purchaseItem->toArray()))
        ->toBe([
            'id',
            'purchase_id',
            'product_id',
            'quantity',
            'unit_cost',
            'subtotal',
            'created_at',
            'updated_at',
        ]);
});

test('purchase relationship returns belongs to', function (): void {
    $purchaseItem = new PurchaseItem();

    expect($purchaseItem->purchase())
        ->toBeInstanceOf(BelongsTo::class);
});

test('product relationship returns belongs to', function (): void {
    $purchaseItem = new PurchaseItem();

    expect($purchaseItem->product())
        ->toBeInstanceOf(BelongsTo::class);
});

test('inventory batch relationship returns has one', function (): void {
    $purchaseItem = new PurchaseItem();

    expect($purchaseItem->inventoryBatch())
        ->toBeInstanceOf(HasOne::class);
});

test('casts returns correct array', function (): void {
    $purchaseItem = new PurchaseItem();

    expect($purchaseItem->casts())
        ->toBe([
            'id' => 'integer',
            'purchase_id' => 'integer',
            'product_id' => 'integer',
            'quantity' => 'integer',
            'unit_cost' => 'integer',
            'subtotal' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $purchaseItem = PurchaseItem::factory()->create()->refresh();

    expect($purchaseItem->id)->toBeInt()
        ->and($purchaseItem->quantity)->toBeInt()
        ->and($purchaseItem->unit_cost)->toBeInt()
        ->and($purchaseItem->subtotal)->toBeInt()
        ->and($purchaseItem->created_at)->toBeInstanceOf(DateTimeInterface::class);
});
