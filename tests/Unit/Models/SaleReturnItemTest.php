<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Store;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $sale = Sale::factory()->create(['created_by' => $user->id, 'store_id' => $store->id]);
    $product = Product::factory()->create(['created_by' => $user->id]);
    $saleItem = SaleItem::factory()->create(['sale_id' => $sale->id, 'product_id' => $product->id]);
    $saleReturn = SaleReturn::factory()->create(['sale_id' => $sale->id, 'store_id' => $store->id, 'created_by' => $user->id]);

    $returnItem = SaleReturnItem::factory()->create([
        'sale_return_id' => $saleReturn->id,
        'product_id' => $product->id,
        'sale_item_id' => $saleItem->id,
    ])->refresh();

    expect(array_keys($returnItem->toArray()))
        ->toBe([
            'id',
            'quantity',
            'price',
            'cost',
            'discount',
            'tax_amount',
            'total',
            'sale_return_id',
            'product_id',
            'sale_item_id',
            'created_at',
            'updated_at',
        ]);
});

test('sale return item relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $sale = Sale::factory()->create(['created_by' => $user->id, 'store_id' => $store->id]);
    $product = Product::factory()->create(['created_by' => $user->id]);
    $saleItem = SaleItem::factory()->create(['sale_id' => $sale->id, 'product_id' => $product->id]);
    $saleReturn = SaleReturn::factory()->create(['sale_id' => $sale->id, 'store_id' => $store->id, 'created_by' => $user->id]);

    $returnItem = SaleReturnItem::factory()->create([
        'sale_return_id' => $saleReturn->id,
        'product_id' => $product->id,
        'sale_item_id' => $saleItem->id,
    ])->refresh();

    expect($returnItem->saleReturn->id)->toBe($saleReturn->id)
        ->and($returnItem->product->id)->toBe($product->id)
        ->and($returnItem->saleItem->id)->toBe($saleItem->id);
});
