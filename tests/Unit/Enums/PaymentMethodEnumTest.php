<?php

declare(strict_types=1);

use App\Enums\PaymentMethodEnum;

it('return all payment methods', function (): void {
    expect(PaymentMethodEnum::cases())->toBeArray();
});

it('payment method label', function (): void {
    $value1 = 'Cash';
    $value2 = 'Card';
    $value3 = 'Transfer';

    expect(PaymentMethodEnum::CASH->label())->toBe($value1)
        ->and(PaymentMethodEnum::CARD->label())->toBe($value2)
        ->and(PaymentMethodEnum::TRANSFER->label())->toBe($value3);
});

it('payment method icon', function (): void {
    $value1 = 'banknotes';
    $value2 = 'credit-card';
    $value3 = 'arrow-right-left';
    expect(PaymentMethodEnum::CASH->icon())->toBe($value1)
        ->and(PaymentMethodEnum::CARD->icon())->toBe($value2)
        ->and(PaymentMethodEnum::TRANSFER->icon())->toBe($value3);
});

it('payment method description', function (): void {
    $value1 = 'Physical cash payment';
    $value2 = 'Credit or debit card payment';
    $value3 = 'Bank transfer or wire';

    expect(PaymentMethodEnum::CASH->description())->toBe($value1)
        ->and(PaymentMethodEnum::CARD->description())->toBe($value2)
        ->and(PaymentMethodEnum::TRANSFER->description())->toBe($value3);
});

it('payment method to array', function (): void {
    expect(PaymentMethodEnum::toArray())->toBeArray();
});

it('payment method helpers', function (): void {
    expect(PaymentMethodEnum::CASH->isCash())->toBeTrue()
        ->and(PaymentMethodEnum::CASH->isCard())->toBeFalse()
        ->and(PaymentMethodEnum::CASH->isTransfer())->toBeFalse()
        ->and(PaymentMethodEnum::CARD->isCard())->toBeTrue()
        ->and(PaymentMethodEnum::CARD->isCash())->toBeFalse()
        ->and(PaymentMethodEnum::CARD->isTransfer())->toBeFalse()
        ->and(PaymentMethodEnum::TRANSFER->isTransfer())->toBeTrue()
        ->and(PaymentMethodEnum::TRANSFER->isCash())->toBeFalse()
        ->and(PaymentMethodEnum::TRANSFER->isCard())->toBeFalse();
});
