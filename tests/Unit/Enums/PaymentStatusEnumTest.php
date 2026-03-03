<?php

declare(strict_types=1);

use App\Enums\PaymentStatusEnum;

it('payment status to array', function (): void {
    expect(PaymentStatusEnum::toArray())->toBeArray();
});

it('payment status label', function (): void {
    $value1 = 'Unpaid';
    $value2 = 'Partial';
    $value3 = 'Paid';

    expect(PaymentStatusEnum::Unpaid->label())->toBe($value1)
        ->and(PaymentStatusEnum::Partial->label())->toBe($value2)
        ->and(PaymentStatusEnum::Paid->label())->toBe($value3);
});

it('can accept payment returns true for unpaid', function (): void {
    expect(PaymentStatusEnum::Unpaid->canAcceptPayment())->toBeTrue();
});

it('can accept payment returns true for partial', function (): void {
    expect(PaymentStatusEnum::Partial->canAcceptPayment())->toBeTrue();
});

it('can accept payment returns false for paid', function (): void {
    expect(PaymentStatusEnum::Paid->canAcceptPayment())->toBeFalse();
});
