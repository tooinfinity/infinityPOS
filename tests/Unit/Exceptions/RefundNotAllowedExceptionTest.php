<?php

declare(strict_types=1);

use App\Exceptions\RefundNotAllowedException;

it('creates exception with entity and reason', function (): void {
    $exception = new RefundNotAllowedException('sale return', 'Sale return must be completed before issuing a refund.');

    expect($exception->getMessage())
        ->toBe('Cannot refund sale return. Sale return must be completed before issuing a refund.');
    expect($exception->entity)->toBe('sale return');
    expect($exception->reason)->toBe('Sale return must be completed before issuing a refund.');
});
