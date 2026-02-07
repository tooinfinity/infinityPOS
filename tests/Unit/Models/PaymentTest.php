<?php

declare(strict_types=1);

use App\Models\Payment;

test('to array', function (): void {
    $payment = Payment::factory()->create()->refresh();

    expect(array_keys($payment->toArray()))
        ->toBe([
            'id',
            'payment_method_id',
            'user_id',
            'reference_no',
            'payable_type',
            'payable_id',
            'amount',
            'payment_date',
            'note',
            'created_at',
            'updated_at',
        ]);
});
