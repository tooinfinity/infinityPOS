<?php

declare(strict_types=1);

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
