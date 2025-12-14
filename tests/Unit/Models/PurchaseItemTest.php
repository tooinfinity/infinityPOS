<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Store;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create()->refresh();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $product = Product::factory()->create(['created_by' => $user->id]);
    $purchase = Purchase::factory()->create(['store_id' => $store->id, 'created_by' => $user->id])->refresh();

    $purchaseItems = PurchaseItem::factory()->create(['product_id' => $product->id, 'purchase_id' => $purchase->id])->refresh();

    expect(array_keys($purchaseItems->toArray()))
        ->toBe([
            'id',
            'quantity',
            'cost',
            'discount',
            'tax_amount',
            'total',
            'batch_number',
            'expiry_date',
            'purchase_id',
            'product_id',
            'created_at',
            'updated_at',
        ]);
});

test('purchase items relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $product = Product::factory()->create(['created_by' => $user->id]);
    $purchase = Purchase::factory()->create(['store_id' => $store->id, 'created_by' => $user->id])->refresh();

    $purchaseItems = PurchaseItem::factory()->create(['product_id' => $product->id, 'purchase_id' => $purchase->id])->refresh();

    $purchaseReturn = PurchaseReturn::factory()->create(['purchase_id' => $purchase->id, 'store_id' => $store->id, 'created_by' => $user->id])->refresh();
    PurchaseReturnItem::factory()->create(['purchase_item_id' => $purchaseItems->id, 'product_id' => $product->id, 'purchase_return_id' => $purchaseReturn->id])->refresh();

    expect($purchaseItems->product->id)->toBe($product->id)
        ->and($purchaseItems->purchase->id)->toBe($purchase->id)
        ->and($purchaseItems->returnItems->first()->id)->toBe($purchaseItems->id)
        ->and($purchaseItems->returnItems->count())->toBe(1)
        ->and($purchaseItems->returnItems->first()->product->id)->toBe($product->id);
});
