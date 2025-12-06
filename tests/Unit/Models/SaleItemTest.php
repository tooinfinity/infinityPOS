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

    $saleItem = SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ])->refresh();

    expect(array_keys($saleItem->toArray()))
        ->toBe([
            'id',
            'sale_id',
            'product_id',
            'quantity',
            'price',
            'cost',
            'discount',
            'tax_amount',
            'total',
            'batch_number',
            'expiry_date',
            'created_at',
            'updated_at',
        ]);
});

test('sale item relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $sale = Sale::factory()->create(['created_by' => $user->id, 'store_id' => $store->id]);
    $product = Product::factory()->create(['created_by' => $user->id]);

    $saleItem = SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ])->refresh();

    $saleReturn = SaleReturn::factory()->create(['sale_id' => $sale->id, 'store_id' => $store->id, 'created_by' => $user->id]);
    $returnItem = SaleReturnItem::factory()->create([
        'sale_return_id' => $saleReturn->id,
        'product_id' => $product->id,
        'sale_item_id' => $saleItem->id,
    ]);

    expect($saleItem->sale->id)->toBe($sale->id)
        ->and($saleItem->product->id)->toBe($product->id)
        ->and($saleItem->returnItems->count())->toBe(1)
        ->and($saleItem->returnItems->first()->id)->toBe($returnItem->id);
});
