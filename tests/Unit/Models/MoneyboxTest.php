<?php

declare(strict_types=1);

use App\Models\Moneybox;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();

    $moneybox = Moneybox::factory()->create(['created_by' => $user->id])->refresh();

    expect(array_keys($moneybox->toArray()))
        ->toBe([
            'id',
            'name',
            'type',
            'description',
            'balance',
            'bank_name',
            'account_number',
            'is_active',
            'store_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});
