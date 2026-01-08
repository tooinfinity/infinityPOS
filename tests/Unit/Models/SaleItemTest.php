<?php

declare(strict_types=1);

use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('to array', function (): void {
    $saleItem = SaleItem::factory()->create()->refresh();

    expect(array_keys($saleItem->toArray()))
        ->toBe([
            'id',
            'sale_id',
            'product_id',
            'quantity',
            'unit_price',
            'unit_cost',
            'subtotal',
            'profit',
            'created_at',
            'updated_at',
        ]);
});

test('sale relationship returns belongs to', function (): void {
    $saleItem = new SaleItem();

    expect($saleItem->sale())
        ->toBeInstanceOf(BelongsTo::class);
});

test('product relationship returns belongs to', function (): void {
    $saleItem = new SaleItem();

    expect($saleItem->product())
        ->toBeInstanceOf(BelongsTo::class);
});

test('batches used relationship returns has many', function (): void {
    $saleItem = new SaleItem();

    expect($saleItem->batchesUsed())
        ->toBeInstanceOf(HasMany::class);
});

test('return items relationship returns has many', function (): void {
    $saleItem = new SaleItem();

    expect($saleItem->returnItems())
        ->toBeInstanceOf(HasMany::class);
});

test('casts returns correct array', function (): void {
    $saleItem = new SaleItem();

    expect($saleItem->casts())
        ->toBe([
            'id' => 'integer',
            'sale_id' => 'integer',
            'product_id' => 'integer',
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'unit_cost' => 'integer',
            'subtotal' => 'integer',
            'profit' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $saleItem = SaleItem::factory()->create()->refresh();

    expect($saleItem->id)->toBeInt()
        ->and($saleItem->quantity)->toBeInt()
        ->and($saleItem->profit)->toBeInt()
        ->and($saleItem->created_at)->toBeInstanceOf(DateTimeInterface::class);
});
