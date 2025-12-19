<?php

declare(strict_types=1);

use App\Models\BusinessIdentifier;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();

    $supplier = Supplier::factory()->create(['created_by' => $user->id])->refresh();

    expect(array_keys($supplier->toArray()))
        ->toBe([
            'id',
            'name',
            'phone',
            'email',
            'address',
            'is_active',
            'business_identifier_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});

test('supplier relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $businessIdentifier = BusinessIdentifier::factory()->create();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $supplier = Supplier::factory()->create(['business_identifier_id' => $businessIdentifier->id, 'created_by' => $user->id]);
    $supplier->update(['updated_by' => $user->id]);

    $purchases = Purchase::factory()->create([
        'supplier_id' => $supplier->id,
        'store_id' => $store->id,
        'created_by' => $user->id,
    ])->refresh();
    $purchaseReturns = PurchaseReturn::factory()->create([
        'supplier_id' => $supplier->id,
        'store_id' => $store->id,
        'created_by' => $user->id,
    ])->refresh();

    expect($supplier->creator->id)->toBe($user->id)
        ->and($supplier->updater->id)->toBe($user->id)
        ->and($supplier->purchases->first()->id)->toBe($purchases->id)
        ->and($supplier->purchaseReturns->first()->id)->toBe($purchaseReturns->id)
        ->and($supplier->businessIdentifier->id)->toBe($businessIdentifier->id);
});
