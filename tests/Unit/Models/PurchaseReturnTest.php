<?php

declare(strict_types=1);

use App\Models\PurchaseReturn;
use App\Models\Store;
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
