<?php

declare(strict_types=1);

use App\Models\Payment;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();

    $payment = Payment::factory()->create(['created_by' => $user->id])->refresh();

    expect(array_keys($payment->toArray()))
        ->toBe([
            'id',
            'reference',
            'type',
            'amount',
            'method',
            'notes',
            'related_id',
            'moneybox_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});
