<?php

declare(strict_types=1);

use App\Enums\CashTransactionTypeEnum;

describe('CashTransactionTypeEnum', function (): void {
    it('returns all values', function (): void {
        $values = CashTransactionTypeEnum::values();

        expect($values)->toBe([
            'sale',
            'expense',
            'withdrawal',
            'deposit',
            'opening',
            'closing',
        ]);
    });

    it('returns correct label for each case', function (CashTransactionTypeEnum $case, string $expectedLabel): void {
        expect($case->label())->toBe($expectedLabel);
    })->with([
        [CashTransactionTypeEnum::SALE, 'Sale'],
        [CashTransactionTypeEnum::EXPENSE, 'Expense'],
        [CashTransactionTypeEnum::WITHDRAWAL, 'Withdrawal'],
        [CashTransactionTypeEnum::DEPOSIT, 'Deposit'],
        [CashTransactionTypeEnum::OPENING, 'Opening Balance'],
        [CashTransactionTypeEnum::CLOSING, 'Closing Balance'],
    ]);

    it('returns correct color for each case', function (CashTransactionTypeEnum $case, string $expectedColor): void {
        expect($case->color())->toBe($expectedColor);
    })->with([
        [CashTransactionTypeEnum::SALE, 'green'],
        [CashTransactionTypeEnum::EXPENSE, 'red'],
        [CashTransactionTypeEnum::WITHDRAWAL, 'orange'],
        [CashTransactionTypeEnum::DEPOSIT, 'blue'],
        [CashTransactionTypeEnum::OPENING, 'purple'],
        [CashTransactionTypeEnum::CLOSING, 'yellow'],
    ]);

    it('returns correct icon for each case', function (CashTransactionTypeEnum $case, string $expectedIcon): void {
        expect($case->icon())->toBe($expectedIcon);
    })->with([
        [CashTransactionTypeEnum::SALE, 'trending-up'],
        [CashTransactionTypeEnum::EXPENSE, 'trending-down'],
        [CashTransactionTypeEnum::WITHDRAWAL, 'arrow-down-circle'],
        [CashTransactionTypeEnum::DEPOSIT, 'arrow-up-circle'],
        [CashTransactionTypeEnum::OPENING, 'unlock'],
        [CashTransactionTypeEnum::CLOSING, 'lock'],
    ]);

    it('correctly identifies inflow transactions', function (CashTransactionTypeEnum $case, bool $expectedResult): void {
        expect($case->isInflow())->toBe($expectedResult);
    })->with([
        [CashTransactionTypeEnum::SALE, true],
        [CashTransactionTypeEnum::EXPENSE, false],
        [CashTransactionTypeEnum::WITHDRAWAL, false],
        [CashTransactionTypeEnum::DEPOSIT, true],
        [CashTransactionTypeEnum::OPENING, true],
        [CashTransactionTypeEnum::CLOSING, false],
    ]);

    it('correctly identifies outflow transactions', function (CashTransactionTypeEnum $case, bool $expectedResult): void {
        expect($case->isOutflow())->toBe($expectedResult);
    })->with([
        [CashTransactionTypeEnum::SALE, false],
        [CashTransactionTypeEnum::EXPENSE, true],
        [CashTransactionTypeEnum::WITHDRAWAL, true],
        [CashTransactionTypeEnum::DEPOSIT, false],
        [CashTransactionTypeEnum::OPENING, false],
        [CashTransactionTypeEnum::CLOSING, false],
    ]);
});
