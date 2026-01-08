<?php

declare(strict_types=1);

use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('to array', function (): void {
    $invoiceItem = InvoiceItem::factory()->create()->refresh();

    expect(array_keys($invoiceItem->toArray()))
        ->toBe([
            'id',
            'invoice_id',
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

test('invoice relationship returns belongs to', function (): void {
    $invoiceItem = new InvoiceItem();

    expect($invoiceItem->invoice())
        ->toBeInstanceOf(BelongsTo::class);
});

test('product relationship returns belongs to', function (): void {
    $invoiceItem = new InvoiceItem();

    expect($invoiceItem->product())
        ->toBeInstanceOf(BelongsTo::class);
});

test('return items relationship returns has many', function (): void {
    $invoiceItem = new InvoiceItem();

    expect($invoiceItem->returnItems())
        ->toBeInstanceOf(HasMany::class);
});

test('casts returns correct array', function (): void {
    $invoiceItem = new InvoiceItem();

    expect($invoiceItem->casts())
        ->toBe([
            'id' => 'integer',
            'invoice_id' => 'integer',
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
    $invoiceItem = InvoiceItem::factory()->create()->refresh();

    expect($invoiceItem->id)->toBeInt()
        ->and($invoiceItem->quantity)->toBeInt()
        ->and($invoiceItem->unit_price)->toBeInt()
        ->and($invoiceItem->profit)->toBeInt()
        ->and($invoiceItem->created_at)->toBeInstanceOf(DateTimeInterface::class);
});
