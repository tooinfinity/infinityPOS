<?php

declare(strict_types=1);

use App\Models\Purchase;
use App\Models\Store;
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
            'created_by',
            'updated_by',
            'supplier_id',
            'store_id',
            'created_at',
            'updated_at',
        ]);
});
