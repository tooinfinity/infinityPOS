<?php

declare(strict_types=1);

use App\Exceptions\InvalidOperationException;

it('creates exception with operation, entity and reason', function (): void {
    $exception = new InvalidOperationException('delete', 'PaymentMethod', 'Cannot delete payment method with associated payments.');

    expect($exception->getMessage())
        ->toBe('Cannot delete PaymentMethod. Cannot delete payment method with associated payments.');
    expect($exception->operation)->toBe('delete');
    expect($exception->entity)->toBe('PaymentMethod');
    expect($exception->reason)->toBe('Cannot delete payment method with associated payments.');
});
