<?php

declare(strict_types=1);

use App\Models\ReturnItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

test('to array', function (): void {
    $returnItem = ReturnItem::factory()->create()->refresh();

    expect(array_keys($returnItem->toArray()))
        ->toBe([
            'id',
            'return_id',
            'sale_item_id',
            'invoice_item_id',
            'product_id',
            'quantity',
            'unit_price',
            'unit_cost',
            'subtotal',
            'created_at',
            'updated_at',
        ]);
});

test('return relationship returns belongs to', function (): void {
    $returnItem = new ReturnItem();

    expect($returnItem->return())
        ->toBeInstanceOf(BelongsTo::class);
});

test('sale item relationship returns belongs to', function (): void {
    $returnItem = new ReturnItem();

    expect($returnItem->saleItem())
        ->toBeInstanceOf(BelongsTo::class);
});

test('invoice item relationship returns belongs to', function (): void {
    $returnItem = new ReturnItem();

    expect($returnItem->invoiceItem())
        ->toBeInstanceOf(BelongsTo::class);
});

test('product relationship returns belongs to', function (): void {
    $returnItem = new ReturnItem();

    expect($returnItem->product())
        ->toBeInstanceOf(BelongsTo::class);
});

test('casts returns correct array', function (): void {
    $returnItem = new ReturnItem();

    expect($returnItem->casts())
        ->toBe([
            'id' => 'integer',
            'return_id' => 'integer',
            'sale_item_id' => 'integer',
            'invoice_item_id' => 'integer',
            'product_id' => 'integer',
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'unit_cost' => 'integer',
            'subtotal' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $returnItem = ReturnItem::factory()->create()->refresh();

    expect($returnItem->id)->toBeInt()
        ->and($returnItem->quantity)->toBeInt()
        ->and($returnItem->unit_price)->toBeInt()
        ->and($returnItem->subtotal)->toBeInt()
        ->and($returnItem->created_at)->toBeInstanceOf(DateTimeInterface::class);
});
