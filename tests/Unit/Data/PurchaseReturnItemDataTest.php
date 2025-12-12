<?php

declare(strict_types=1);

use App\Data\ProductData;
use App\Data\PurchaseItemData;
use App\Data\PurchaseReturnData;
use App\Data\PurchaseReturnItemData;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;

it('transforms a purchase return item model into PurchaseReturnItemData', function (): void {
    $return = PurchaseReturn::factory()->create();
    $product = Product::factory()->create();
    $purchaseItem = PurchaseItem::factory()->create();

    /** @var PurchaseReturnItem $item */
    $item = PurchaseReturnItem::factory()
        ->for($return, 'purchaseReturn')
        ->for($product, 'product')
        ->for($purchaseItem, 'purchaseItem')
        ->create([
            'quantity' => 4,
            'cost' => 120,
            'total' => 480,
            'batch_number' => 'RB-004',
        ]);

    $data = PurchaseReturnItemData::from(
        $item->load(['purchaseReturn', 'product', 'purchaseItem'])
    );

    expect($data)
        ->toBeInstanceOf(PurchaseReturnItemData::class)
        ->id->toBe($item->id)
        ->quantity->toBe(4)
        ->cost->toBe(120)
        ->total->toBe(480)
        ->batch_number->toBe('RB-004')
        ->and($data->purchaseReturn->resolve())
        ->toBeInstanceOf(PurchaseReturnData::class)
        ->id->toBe($return->id)
        ->and($data->product->resolve())
        ->toBeInstanceOf(ProductData::class)
        ->id->toBe($product->id)
        ->and($data->purchaseItem->resolve())
        ->toBeInstanceOf(PurchaseItemData::class)
        ->id->toBe($purchaseItem->id)
        ->and($data->created_at)
        ->toBe($item->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($item->updated_at->toDateTimeString());
});
