<?php

declare(strict_types=1);

use App\Models\StockTransfer;
use App\Models\Store;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();
    $fromStore = Store::factory()->create(['created_by' => $user->id]);
    $toStore = Store::factory()->create(['created_by' => $user->id]);

    $stockTransfer = StockTransfer::factory()->create([
        'created_by' => $user->id,
        'from_store_id' => $fromStore->id,
        'to_store_id' => $toStore->id,
    ])->refresh();

    expect(array_keys($stockTransfer->toArray()))
        ->toBe([
            'id',
            'reference',
            'status',
            'notes',
            'from_store_id',
            'to_store_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});
