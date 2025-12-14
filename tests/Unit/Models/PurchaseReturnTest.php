<?php

declare(strict_types=1);

use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();
    $store = Store::factory()->create(['created_by' => $user->id]);

    $purchaseReturn = PurchaseReturn::factory()->create([
        'created_by' => $user->id,
        'store_id' => $store->id,
    ])->refresh();

    expect(array_keys($purchaseReturn->toArray()))
        ->toBe([
            'id',
            'reference',
            'subtotal',
            'discount',
            'tax',
            'total',
            'refunded',
            'status',
            'reason',
            'notes',
            'purchase_id',
            'supplier_id',
            'store_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});

test('purchase return relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $supplier = Supplier::factory()->create(['created_by' => $user->id]);
    $purchase = Purchase::factory()->create(['created_by' => $user->id, 'store_id' => $store->id, 'supplier_id' => $supplier->id]);

    $purchaseReturn = PurchaseReturn::factory()->create([
        'created_by' => $user->id,
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
        'purchase_id' => $purchase->id,
    ])->refresh();
    $purchaseReturn->update(['updated_by' => $user->id]);

    $product = Product::factory()->create(['created_by' => $user->id]);
    $purchaseItem = PurchaseItem::factory()->create(['purchase_id' => $purchase->id, 'product_id' => $product->id]);
    $returnItem = PurchaseReturnItem::factory()->create([
        'purchase_return_id' => $purchaseReturn->id,
        'product_id' => $product->id,
        'purchase_item_id' => $purchaseItem->id,
    ]);

    $payment = Payment::factory()->create([
        'related_type' => PurchaseReturn::class,
        'related_id' => $purchaseReturn->id,
        'created_by' => $user->id,
    ]);
    $stockMovement = StockMovement::factory()->create([

        'source_type' => PurchaseReturn::class,
        'source_id' => $purchaseReturn->id,
        'product_id' => $product->id,
        'store_id' => $store->id,
        'created_by' => $user->id,
    ]);

    expect($purchaseReturn->creator->id)->toBe($user->id)
        ->and($purchaseReturn->updater->id)->toBe($user->id)
        ->and($purchaseReturn->store->id)->toBe($store->id)
        ->and($purchaseReturn->supplier->id)->toBe($supplier->id)
        ->and($purchaseReturn->purchase->id)->toBe($purchase->id)
        ->and($purchaseReturn->items->count())->toBe(1)
        ->and($purchaseReturn->items->first()->id)->toBe($returnItem->id)
        ->and($purchaseReturn->payments->count())->toBe(1)
        ->and($purchaseReturn->payments->first()->id)->toBe($payment->id)
        ->and($purchaseReturn->stockMovements->count())->toBe(1)
        ->and($purchaseReturn->stockMovements->first()->id)->toBe($stockMovement->id);
});
