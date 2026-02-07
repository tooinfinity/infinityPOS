<?php

declare(strict_types=1);

use App\Models\PaymentMethod;

test('to array', function (): void {
    $paymentMethod = PaymentMethod::factory()->create()->refresh();

    expect(array_keys($paymentMethod->toArray()))
        ->toBe([
            'id',
            'name',
            'code',
            'is_active',
            'created_at',
            'updated_at',
        ]);
});

test('only returns active payment methods by default', function (): void {
    PaymentMethod::factory()->count(2)->create([
        'is_active' => true,
    ]);
    PaymentMethod::factory()->count(2)->create([
        'is_active' => false,
    ]);

    $paymentMethods = PaymentMethod::all();

    expect($paymentMethods)
        ->toHaveCount(2);
});
