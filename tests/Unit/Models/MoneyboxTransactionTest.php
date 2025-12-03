<?php

declare(strict_types=1);

use App\Models\Moneybox;
use App\Models\MoneyboxTransaction;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();
    $moneybox = Moneybox::factory()->create(['created_by' => $user->id]);

    $transaction = MoneyboxTransaction::factory()->create([
        'created_by' => $user->id,
        'moneybox_id' => $moneybox->id,
    ])->refresh();

    expect(array_keys($transaction->toArray()))
        ->toBe([
            'id',
            'moneybox_id',
            'type',
            'amount',
            'balance_after',
            'reference',
            'notes',
            'payment_id',
            'expense_id',
            'transfer_to_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});
