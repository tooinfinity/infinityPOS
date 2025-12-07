<?php

declare(strict_types=1);

use App\Enums\MoneyboxTypeEnum;

it('return all moneybox types', function (): void {
    expect(MoneyboxTypeEnum::cases())->toBeArray();
});

it('moneybox type label', function (): void {
    $value1 = 'Cash Register';
    $value2 = 'Bank Account';
    $value3 = 'Mobile Money';
    $value4 = 'Other';
    expect(MoneyboxTypeEnum::CASH_REGISTER->label())->toBe($value1)
        ->and(MoneyboxTypeEnum::BANK_ACCOUNT->label())->toBe($value2)
        ->and(MoneyboxTypeEnum::MOBILE_MONEY->label())->toBe($value3)
        ->and(MoneyboxTypeEnum::OTHER->label())->toBe($value4);
});

it('moneybox type icon', function (): void {
    $value1 = 'cash-register';
    $value2 = 'building-library';
    $value3 = 'device-phone-mobile';
    $value4 = 'wallet';
    expect(MoneyboxTypeEnum::CASH_REGISTER->icon())->toBe($value1)
        ->and(MoneyboxTypeEnum::BANK_ACCOUNT->icon())->toBe($value2)
        ->and(MoneyboxTypeEnum::MOBILE_MONEY->icon())->toBe($value3)
        ->and(MoneyboxTypeEnum::OTHER->icon())->toBe($value4);
});

it('moneybox type to array', function (): void {
    expect(MoneyboxTypeEnum::toArray())->toBeArray();
});

it('moneybox type description', function (): void {

    $value1 = 'Physical cash register or drawer';
    $value2 = 'Bank account for transfers';
    $value3 = 'Mobile money service (M-Pesa, etc.)';
    $value4 = 'Other payment method';

    expect(MoneyboxTypeEnum::CASH_REGISTER->description())->toBe($value1)
        ->and(MoneyboxTypeEnum::BANK_ACCOUNT->description())->toBe($value2)
        ->and(MoneyboxTypeEnum::MOBILE_MONEY->description())->toBe($value3)
        ->and(MoneyboxTypeEnum::OTHER->description())->toBe($value4);
});
