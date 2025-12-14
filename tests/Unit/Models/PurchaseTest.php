<?php

declare(strict_types=1);

use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();
    $store = Store::factory()->create(['created_by' => $user->id]);

    $purchase = Purchase::factory()->create([
        'created_by' => $user->id,
        'store_id' => $store->id,
    ])->refresh();

    expect(array_keys($purchase->toArray()))
        ->toBe([
            'id',
            'reference',
            'subtotal',
            'discount',
            'tax',
            'total',
            'paid',
            'status',
            'notes',
            'supplier_id',
            'store_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});

test('purchase relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $supplier = Supplier::factory()->create(['created_by' => $user->id]);
    $purchase = Purchase::factory()->create([
        'created_by' => $user->id,
        'store_id' => $store->id,
        'supplier_id' => $supplier->id,
    ]);
    $purchase->update(['updated_by' => $user->id]);

    $product = Product::factory()->create(['created_by' => $user->id]);
    $items = PurchaseItem::factory()->create(['product_id' => $product->id, 'purchase_id' => $purchase->id]);
    $returns = PurchaseReturn::factory()->create(['store_id' => $store->id, 'supplier_id' => $supplier->id, 'purchase_id' => $purchase->id, 'created_by' => $user->id]);

    $payment = Payment::factory()->forPurchase($purchase->id)->create(['created_by' => $user->id]);
    $stockMovement = StockMovement::factory()->create(['source_type' => Purchase::class, 'source_id' => $purchase->id, 'product_id' => $product->id, 'store_id' => $store->id, 'created_by' => $user->id]);

    expect($purchase->creator->id)->toBe($user->id)
        ->and($purchase->updater->id)->toBe($user->id)
        ->and($purchase->store->id)->toBe($store->id)
        ->and($purchase->supplier->id)->toBe($supplier->id)
        ->and($purchase->items->count())->toBe(1)
        ->and($purchase->items->first()->id)->toBe($items->id)
        ->and($purchase->returns->count())->toBe(1)
        ->and($purchase->returns->first()->id)->toBe($returns->id)
        ->and($purchase->payments->count())->toBe(1)
        ->and($purchase->payments->first()->id)->toBe($payment->id)
        ->and($purchase->stockMovements->count())->toBe(1)
        ->and($purchase->stockMovements->first()->id)->toBe($stockMovement->id);

});
