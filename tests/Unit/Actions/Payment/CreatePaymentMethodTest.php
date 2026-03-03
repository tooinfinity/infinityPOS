<?php

declare(strict_types=1);

use App\Actions\Payment\CreatePaymentMethod;
use App\Data\Payment\CreatePaymentMethodData;
use App\Models\PaymentMethod;

it('may create a payment method', function (): void {
    $action = resolve(CreatePaymentMethod::class);

    $data = new CreatePaymentMethodData(
        name: 'Cash',
        code: 'cash',
        is_active: true,
    );

    $paymentMethod = $action->handle($data);

    expect($paymentMethod)->toBeInstanceOf(PaymentMethod::class)
        ->and($paymentMethod->name)->toBe('Cash')
        ->and($paymentMethod->code)->toBe('cash')
        ->and($paymentMethod->is_active)->toBeTrue()
        ->and($paymentMethod->exists)->toBeTrue();
});

it('creates payment method with is_active false', function (): void {
    $action = resolve(CreatePaymentMethod::class);

    $data = new CreatePaymentMethodData(
        name: 'Inactive',
        code: 'inactive',
        is_active: false,
    );

    $paymentMethod = $action->handle($data);

    expect($paymentMethod->is_active)->toBeFalse();
});

it('stores payment method in database', function (): void {
    $action = resolve(CreatePaymentMethod::class);

    $data = new CreatePaymentMethodData(
        name: 'Bank Transfer',
        code: 'bank_transfer',
        is_active: true,
    );

    $paymentMethod = $action->handle($data);

    $this->assertDatabaseHas('payment_methods', [
        'id' => $paymentMethod->id,
        'name' => 'Bank Transfer',
        'code' => 'bank_transfer',
        'is_active' => true,
    ]);
});
