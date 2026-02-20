<?php

declare(strict_types=1);

use App\Data\Sale\RecordPaymentData;
use Carbon\CarbonInterface;

it('may be created with required fields', function (): void {
    $data = new RecordPaymentData(
        payment_method_id: 1,
        amount: 5000,
        payment_date: Illuminate\Support\Facades\Date::now(),
        user_id: null,
        note: null,
    );

    expect($data)
        ->payment_method_id->toBe(1)
        ->amount->toBe(5000)
        ->payment_date->toBeInstanceOf(CarbonInterface::class)
        ->user_id->toBeNull()
        ->note->toBeNull();
});

it('may be created with all fields', function (): void {
    $data = new RecordPaymentData(
        payment_method_id: 2,
        amount: 10000,
        payment_date: Illuminate\Support\Facades\Date::parse('2024-01-15'),
        user_id: 5,
        note: 'Payment received',
    );

    expect($data->payment_method_id)->toBe(2)
        ->and($data->amount)->toBe(10000)
        ->and($data->user_id)->toBe(5)
        ->and($data->note)->toBe('Payment received');
});

it('handles string date', function (): void {
    $data = new RecordPaymentData(
        payment_method_id: 1,
        amount: 1000,
        payment_date: '2024-01-20',
        user_id: null,
        note: null,
    );

    expect($data->payment_date)->toBe('2024-01-20');
});
