<?php

declare(strict_types=1);

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
            'balance',
            'is_active',
            'created_by',
            'updated_by',
            'business_identifier_id',
            'created_at',
            'updated_at',
        ]);
});
