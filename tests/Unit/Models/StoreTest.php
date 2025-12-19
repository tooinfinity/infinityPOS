<?php

declare(strict_types=1);

use App\Models\Expense;
use App\Models\Moneybox;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\Store;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();

    $store = Store::factory()->create(['created_by' => $user->id])->refresh();

    expect(array_keys($store->toArray()))
        ->toBe([
            'id',
            'name',
            'city',
            'address',
            'phone',
            'is_active',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});

test('store relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $storeA = Store::factory()->create(['created_by' => $user->id]);
    $storeA->update(['updated_by' => $user->id]);

    $storeB = Store::factory()->create(['created_by' => $user->id]);

    // inventory layer creates product-store availability
    $product = Product::factory()->create(['created_by' => $user->id]);
    App\Models\InventoryLayer::factory()->forProductStore($product, $storeA)->create(['remaining_qty' => 5]);

    // direct hasMany relations
    $sale = Sale::factory()->create(['store_id' => $storeA->id, 'created_by' => $user->id]);
    $purchase = Purchase::factory()->create(['store_id' => $storeA->id, 'created_by' => $user->id]);
    $saleReturn = SaleReturn::factory()->create(['store_id' => $storeA->id, 'created_by' => $user->id]);
    $purchaseReturn = PurchaseReturn::factory()->create(['store_id' => $storeA->id, 'created_by' => $user->id]);
    $moneybox = Moneybox::factory()->create(['store_id' => $storeA->id, 'created_by' => $user->id]);
    $expense = Expense::factory()->create(['store_id' => $storeA->id, 'created_by' => $user->id]);
    $movement = StockMovement::factory()->create(['store_id' => $storeA->id, 'product_id' => $product->id, 'created_by' => $user->id]);

    // transfers
    $outgoing = StockTransfer::factory()->create(['from_store_id' => $storeA->id, 'to_store_id' => $storeB->id, 'created_by' => $user->id]);
    $incoming = StockTransfer::factory()->create(['from_store_id' => $storeB->id, 'to_store_id' => $storeA->id, 'created_by' => $user->id]);

    expect($storeA->creator->id)->toBe($user->id)
        ->and($storeA->updater->id)->toBe($user->id)
        // ensure we fetch a product from inventory layers pivot
        ->and($storeA->products()->withoutGlobalScopes()->first()->id)->toBe($product->id)
        ->and((int) $storeA->products()->withoutGlobalScopes()->first()->pivot->remaining_qty)->toBe(5)
        ->and($storeA->sales->first()->id)->toBe($sale->id)
        ->and($storeA->purchases->first()->id)->toBe($purchase->id)
        ->and($storeA->saleReturns->first()->id)->toBe($saleReturn->id)
        ->and($storeA->purchaseReturns->first()->id)->toBe($purchaseReturn->id)
        ->and($storeA->moneyboxes->first()->id)->toBe($moneybox->id)
        ->and($storeA->expenses->first()->id)->toBe($expense->id)
        ->and($storeA->stockMovements->first()->id)->toBe($movement->id)
        ->and($storeA->outgoingTransfers->first()->id)->toBe($outgoing->id)
        ->and($storeA->incomingTransfers->first()->id)->toBe($incoming->id);
});
