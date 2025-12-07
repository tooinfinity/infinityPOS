<?php

declare(strict_types=1);

use App\Enums\PaymentTypeEnum;

it('return all payment types', function (): void {
    expect(PaymentTypeEnum::cases())->toBeArray();
});

it('payment type label', function (): void {
    $value1 = 'Sale';
    $value2 = 'Purchase';
    $value3 = 'Expense';
    $value4 = 'Other';
    expect(PaymentTypeEnum::SALE->label())->toBe($value1)
        ->and(PaymentTypeEnum::PURCHASE->label())->toBe($value2)
        ->and(PaymentTypeEnum::EXPENSE->label())->toBe($value3)
        ->and(PaymentTypeEnum::OTHER->label())->toBe($value4);
});

it('payment type to array', function (): void {
    expect(PaymentTypeEnum::toArray())->toBeArray();
});
