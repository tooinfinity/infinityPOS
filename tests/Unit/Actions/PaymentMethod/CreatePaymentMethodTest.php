<?php

declare(strict_types=1);

use App\Actions\PaymentMethod\CreatePaymentMethod;
use App\Data\Payment\PaymentMethodData;
use App\Models\PaymentMethod;

it('may create a payment method', function (): void {
    $action = resolve(CreatePaymentMethod::class);

    $data = new PaymentMethodData(
        name: 'Credit Card',
        code: 'CC',
        is_active: true,
    );

    $method = $action->handle($data);

    expect($method)->toBeInstanceOf(PaymentMethod::class)
        ->and($method->exists)->toBeTrue()
        ->and($method->name)->toBe('Credit Card')
        ->and($method->code)->toBe('CC')
        ->and($method->is_active)->toBeTrue();
});

it('creates inactive payment method', function (): void {
    $action = resolve(CreatePaymentMethod::class);

    $data = new PaymentMethodData(
        name: 'Inactive Method',
        code: 'IM',
        is_active: false,
    );

    $method = $action->handle($data);

    expect($method->is_active)->toBeFalse();
});

it('creates payment method with code', function (): void {
    $action = resolve(CreatePaymentMethod::class);

    $data = new PaymentMethodData(
        name: 'Bank Transfer',
        code: 'BT',
        is_active: true,
    );

    $method = $action->handle($data);

    expect($method->code)->toBe('BT');
});
