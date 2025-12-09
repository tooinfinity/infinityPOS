<?php

declare(strict_types=1);

use App\Enums\PaymentTypeEnum;

it('return all payment types', function (): void {
    expect(PaymentTypeEnum::cases())->toBeArray();
});

it('payment type label', function (): void {
    $value1 = 'Sale';
    $value2 = 'Invoice';
    $value3 = 'Purchase';
    $value4 = 'Expense';
    $value5 = 'Other';
    expect(PaymentTypeEnum::SALE->label())->toBe($value1)
        ->and(PaymentTypeEnum::INVOICE->label())->toBe($value2)
        ->and(PaymentTypeEnum::PURCHASE->label())->toBe($value3)
        ->and(PaymentTypeEnum::EXPENSE->label())->toBe($value4)
        ->and(PaymentTypeEnum::OTHER->label())->toBe($value5);
});

it('payment type to array', function (): void {
    expect(PaymentTypeEnum::toArray())->toBeArray();
});
