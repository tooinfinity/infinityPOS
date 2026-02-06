<?php

declare(strict_types=1);

use App\Enums\PaymentStatusEnum;

it('Payment status to array', function (): void {
    expect(PaymentStatusEnum::toArray())->toBeArray();
});

it('Payment status label', function (): void {
    $value1 = 'Unpaid';
    $value2 = 'Partial';
    $value3 = 'Paid';

    expect(PaymentStatusEnum::Unpaid->label())->toBe($value1)
        ->and(PaymentStatusEnum::Partial->label())->toBe($value2)
        ->and(PaymentStatusEnum::Paid->label())->toBe($value3);
});
