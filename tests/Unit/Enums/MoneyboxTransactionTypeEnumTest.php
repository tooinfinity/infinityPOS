<?php

declare(strict_types=1);

use App\Enums\MoneyboxTransactionTypeEnum;

it('return all moneybox transaction types', function (): void {
    expect(MoneyboxTransactionTypeEnum::cases())->toBeArray();
});

it('moneybox transaction type label', function (): void {
    $value1 = 'In';
    $value2 = 'Out';
    $value3 = 'Transfer';
    expect(MoneyboxTransactionTypeEnum::IN->label())->toBe($value1)
        ->and(MoneyboxTransactionTypeEnum::OUT->label())->toBe($value2)
        ->and(MoneyboxTransactionTypeEnum::TRANSFER->label())->toBe($value3);
});

it('moneybox transaction type color', function (): void {
    $value1 = 'green';
    $value2 = 'red';
    $value3 = 'blue';
    expect(MoneyboxTransactionTypeEnum::IN->color())->toBe($value1)
        ->and(MoneyboxTransactionTypeEnum::OUT->color())->toBe($value2)
        ->and(MoneyboxTransactionTypeEnum::TRANSFER->color())->toBe($value3);
});

it('moneybox transaction type icon', function (): void {
    $value1 = 'arrow-down';
    $value2 = 'arrow-up';
    $value3 = 'arrow-right-left';
    expect(MoneyboxTransactionTypeEnum::IN->icon())->toBe($value1)
        ->and(MoneyboxTransactionTypeEnum::OUT->icon())->toBe($value2)
        ->and(MoneyboxTransactionTypeEnum::TRANSFER->icon())->toBe($value3);
});

it('moneybox transaction type to array', function (): void {
    expect(MoneyboxTransactionTypeEnum::toArray())->toBeArray();
});
