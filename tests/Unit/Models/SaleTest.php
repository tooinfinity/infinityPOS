<?php

declare(strict_types=1);

use App\Models\Sale;
use App\Models\Store;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();
    $store = Store::factory()->create(['created_by' => $user->id]);

    $sale = Sale::factory()->create([
        'created_by' => $user->id,
        'store_id' => $store->id,
    ])->refresh();

    expect(array_keys($sale->toArray()))
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
            'client_id',
            'store_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});
