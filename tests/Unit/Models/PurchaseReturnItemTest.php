<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $supplier = Supplier::factory()->create(['created_by' => $user->id]);
    $purchase = Purchase::factory()->create([
        'created_by' => $user->id,
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
    ]);
    $product = Product::factory()->create(['created_by' => $user->id]);
    $purchaseItem = PurchaseItem::factory()->create(['purchase_id' => $purchase->id, 'product_id' => $product->id]);
    $purchaseReturn = PurchaseReturn::factory()->create([
        'created_by' => $user->id,
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'purchase_id' => $purchase->id,
    ]);

    $returnItem = PurchaseReturnItem::factory()->create([
        'purchase_return_id' => $purchaseReturn->id,
        'product_id' => $product->id,
        'purchase_item_id' => $purchaseItem->id,
    ])->refresh();

    expect(array_keys($returnItem->toArray()))
        ->toBe([
            'id',
            'quantity',
            'cost',
            'total',
            'batch_number',
            'purchase_return_id',
            'product_id',
            'purchase_item_id',
            'created_at',
            'updated_at',
        ]);
});

test('purchase return item relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $supplier = Supplier::factory()->create(['created_by' => $user->id]);
    $purchase = Purchase::factory()->create(['created_by' => $user->id, 'store_id' => $store->id, 'supplier_id' => $supplier->id]);
    $product = Product::factory()->create(['created_by' => $user->id]);
    $purchaseItem = PurchaseItem::factory()->create(['purchase_id' => $purchase->id, 'product_id' => $product->id]);
    $purchaseReturn = PurchaseReturn::factory()->create(['purchase_id' => $purchase->id, 'store_id' => $store->id, 'supplier_id' => $supplier->id, 'created_by' => $user->id]);

    $returnItem = PurchaseReturnItem::factory()->create([
        'purchase_return_id' => $purchaseReturn->id,
        'product_id' => $product->id,
        'purchase_item_id' => $purchaseItem->id,
    ])->refresh();

    expect($returnItem->purchaseReturn->id)->toBe($purchaseReturn->id)
        ->and($returnItem->product->id)->toBe($product->id)
        ->and($returnItem->purchaseItem->id)->toBe($purchaseItem->id);
});
