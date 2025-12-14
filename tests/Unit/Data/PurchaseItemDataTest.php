<?php

declare(strict_types=1);

use App\Data\ProductData;
use App\Data\PurchaseItemData;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;

it('transforms a purchase item model into PurchaseItemData', function (): void {
    $purchase = Purchase::factory()->create();
    $product = Product::factory()->create();

    /** @var PurchaseItem $item */
    $item = PurchaseItem::factory()
        ->for($purchase, 'purchase')
        ->for($product, 'product')
        ->create([
            'quantity' => 6,
            'cost' => 250,
            'discount' => 50,
            'tax_amount' => 100,
            'total' => 1550,
            'batch_number' => 'PB-001',
            'expiry_date' => null,
            
        ]);

    $data = PurchaseItemData::from(
        $item->load(['product'])
    );

    expect($data)
        ->toBeInstanceOf(PurchaseItemData::class)
        ->id->toBe($item->id)
        ->quantity->toBe(6)
        ->cost->toBe(250)
        ->discount->toBe(50)
        ->tax_amount->toBe(100)
        ->total->toBe(1550)
        ->batch_number->toBe('PB-001')
        ->expiry_date->toBeNull()
                ->and($data->product->resolve())
        ->toBeInstanceOf(ProductData::class)
        ->id->toBe($product->id)
        ->and($data->created_at)
        ->toBe($item->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($item->updated_at->toDateTimeString());
});
