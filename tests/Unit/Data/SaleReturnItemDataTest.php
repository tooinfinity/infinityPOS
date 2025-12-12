<?php

declare(strict_types=1);

use App\Data\ProductData;
use App\Data\SaleItemData;
use App\Data\SaleReturnData;
use App\Data\SaleReturnItemData;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;

it('transforms a sale return item model into SaleReturnItemData', function (): void {
    $saleReturn = SaleReturn::factory()->create();
    $product = Product::factory()->create();
    $saleItem = SaleItem::factory()->create();

    /** @var SaleReturnItem $item */
    $item = SaleReturnItem::factory()
        ->for($saleReturn, 'saleReturn')
        ->for($product, 'product')
        ->for($saleItem, 'saleItem')
        ->create([
            'quantity' => 2,
            'price' => 1500,
            'cost' => 1000,
            'discount' => 0,
            'tax_amount' => 300,
            'total' => 2800,
        ]);

    $data = SaleReturnItemData::from(
        $item->load(['saleReturn', 'product', 'saleItem'])
    );

    expect($data)
        ->toBeInstanceOf(SaleReturnItemData::class)
        ->id->toBe($item->id)
        ->quantity->toBe(2)
        ->price->toBe(1500)
        ->cost->toBe(1000)
        ->discount->toBe(0)
        ->tax_amount->toBe(300)
        ->total->toBe(2800)
        ->and($data->saleReturn->resolve())
        ->toBeInstanceOf(SaleReturnData::class)
        ->id->toBe($saleReturn->id)
        ->and($data->product->resolve())
        ->toBeInstanceOf(ProductData::class)
        ->id->toBe($product->id)
        ->and($data->saleItem->resolve())
        ->toBeInstanceOf(SaleItemData::class)
        ->id->toBe($saleItem->id)
        ->and($data->created_at)
        ->toBe($item->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($item->updated_at->toDateTimeString());
});
