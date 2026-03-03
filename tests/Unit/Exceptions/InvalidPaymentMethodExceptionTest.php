<?php

declare(strict_types=1);

use App\Exceptions\InvalidPaymentMethodException;

it('creates exception with method id', function (): void {
    $exception = new InvalidPaymentMethodException(1);

    expect($exception->getMessage())
        ->toBe('Payment method 1 is not active or does not exist');
    expect($exception->methodId)->toBe(1);
    expect($exception->reason)->toBeNull();
});

it('creates exception with custom reason', function (): void {
    $exception = new InvalidPaymentMethodException(1, 'Payment method is inactive');

    expect($exception->getMessage())
        ->toBe('Payment method 1: Payment method is inactive');
    expect($exception->reason)->toBe('Payment method is inactive');
});
