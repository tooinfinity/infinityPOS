<?php

declare(strict_types=1);

use App\Enums\PaymentMethodEnum;

describe('PaymentMethodEnum', function (): void {
    it('returns all values', function (): void {
        $values = PaymentMethodEnum::values();

        expect($values)->toBe([
            'cash',
            'card',
            'bank_transfer',
            'check',
            'split',
        ]);
    });

    it('returns all options with value and label pairs', function (): void {
        $options = PaymentMethodEnum::options();

        expect($options)->toBe([
            ['value' => 'cash', 'label' => 'Cash'],
            ['value' => 'card', 'label' => 'Card'],
            ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
            ['value' => 'check', 'label' => 'Check'],
            ['value' => 'split', 'label' => 'Split Payment'],
        ]);
    });

    it('returns POS options', function (): void {
        $posOptions = PaymentMethodEnum::posOptions();

        expect($posOptions)->toBe([
            PaymentMethodEnum::CASH,
            PaymentMethodEnum::CARD,
            PaymentMethodEnum::SPLIT,
        ]);
    });

    it('returns correct label for each case', function (PaymentMethodEnum $case, string $expectedLabel): void {
        expect($case->label())->toBe($expectedLabel);
    })->with([
        [PaymentMethodEnum::CASH, 'Cash'],
        [PaymentMethodEnum::CARD, 'Card'],
        [PaymentMethodEnum::BANK_TRANSFER, 'Bank Transfer'],
        [PaymentMethodEnum::CHECK, 'Check'],
        [PaymentMethodEnum::SPLIT, 'Split Payment'],
    ]);

    it('returns correct icon for each case', function (PaymentMethodEnum $case, string $expectedIcon): void {
        expect($case->icon())->toBe($expectedIcon);
    })->with([
        [PaymentMethodEnum::CASH, 'banknote'],
        [PaymentMethodEnum::CARD, 'credit-card'],
        [PaymentMethodEnum::BANK_TRANSFER, 'building-2'],
        [PaymentMethodEnum::CHECK, 'file-text'],
        [PaymentMethodEnum::SPLIT, 'split'],
    ]);

    it('returns correct color for each case', function (PaymentMethodEnum $case, string $expectedColor): void {
        expect($case->color())->toBe($expectedColor);
    })->with([
        [PaymentMethodEnum::CASH, 'green'],
        [PaymentMethodEnum::CARD, 'blue'],
        [PaymentMethodEnum::BANK_TRANSFER, 'purple'],
        [PaymentMethodEnum::CHECK, 'orange'],
        [PaymentMethodEnum::SPLIT, 'yellow'],
    ]);

    it('correctly identifies cash payment method', function (PaymentMethodEnum $case, bool $expectedResult): void {
        expect($case->isCash())->toBe($expectedResult);
    })->with([
        [PaymentMethodEnum::CASH, true],
        [PaymentMethodEnum::CARD, false],
        [PaymentMethodEnum::BANK_TRANSFER, false],
        [PaymentMethodEnum::CHECK, false],
        [PaymentMethodEnum::SPLIT, false],
    ]);
});
