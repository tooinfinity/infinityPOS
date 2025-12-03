<?php

declare(strict_types=1);

use App\Models\Invoice;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $sale = Sale::factory()->create([
        'created_by' => $user->id,
        'store_id' => $store->id,
    ]);

    $invoice = Invoice::factory()->create([
        'created_by' => $user->id,
        'sale_id' => $sale->id,
    ])->refresh();

    expect(array_keys($invoice->toArray()))
        ->toBe([
            'id',
            'reference',
            'issued_at',
            'due_at',
            'paid_at',
            'subtotal',
            'discount',
            'tax',
            'total',
            'paid',
            'status',
            'notes',
            'sale_id',
            'client_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});
