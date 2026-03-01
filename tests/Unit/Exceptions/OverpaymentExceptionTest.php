<?php

declare(strict_types=1);

use App\Exceptions\OverpaymentException;

it('creates exception with amount and max allowed', function (): void {
    $exception = new OverpaymentException(150.00, 100.00);

    expect($exception->getMessage())
        ->toBe('Payment amount 150.00 exceeds maximum allowed 100.00');
    expect($exception->amount)->toBe(150.00);
    expect($exception->maxAllowed)->toBe(100.00);
    expect($exception->currentPaid)->toBeNull();
});

it('creates exception with current paid amount', function (): void {
    $exception = new OverpaymentException(150.00, 200.00, 100.00);

    expect($exception->getMessage())
        ->toBe('Payment amount 150.00 exceeds maximum allowed 200.00. Current paid: 100.00');
    expect($exception->currentPaid)->toBe(100.00);
});
