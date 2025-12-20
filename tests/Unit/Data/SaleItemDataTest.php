<?php

declare(strict_types=1);

use App\Data\Products\ProductData;
use App\Data\SaleItemData;
use App\Models\Product;
use App\Models\SaleItem;

it('transforms a sale item model into SaleItemData', function (): void {
    $product = Product::factory()->create();

    /** @var SaleItem $item */
    $item = SaleItem::factory()
        ->for($product, 'product')
        ->create([
            'quantity' => 3,
            'price' => 2000,
            'cost' => 1500,
            'discount' => 100,
            'tax_amount' => 380,
            'total' => 6280,
            'batch_number' => 'B-123',
            'expiry_date' => null,
        ]);

    $data = SaleItemData::from(
        $item->load(['product'])
    );

    expect($data)
        ->toBeInstanceOf(SaleItemData::class)
        ->id->toBe($item->id)
        ->quantity->toBe(3)
        ->price->toBe(2000)
        ->cost->toBe(1500)
        ->discount->toBe(100)
        ->tax_amount->toBe(380)
        ->total->toBe(6280)
        ->batch_number->toBe('B-123')
        ->and($data->product->resolve())
        ->toBeInstanceOf(ProductData::class)
        ->id->toBe($product->id)
        ->and($data->created_at)
        ->toBe($item->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($item->updated_at->toDateTimeString());
});
