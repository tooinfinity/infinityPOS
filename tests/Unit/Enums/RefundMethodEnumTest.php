<?php

declare(strict_types=1);

use App\Enums\RefundMethodEnum;

describe('RefundMethodEnum', function (): void {
    it('returns all values', function (): void {
        $values = RefundMethodEnum::values();

        expect($values)->toBe([
            'cash',
            'card',
            'store_credit',
        ]);
    });

    it('returns all options with value and label pairs', function (): void {
        $options = RefundMethodEnum::options();

        expect($options)->toBe([
            ['value' => 'cash', 'label' => 'Cash'],
            ['value' => 'card', 'label' => 'Card'],
            ['value' => 'store_credit', 'label' => 'Store Credit'],
        ]);
    });

    it('returns correct label for each case', function (RefundMethodEnum $case, string $expectedLabel): void {
        expect($case->label())->toBe($expectedLabel);
    })->with([
        [RefundMethodEnum::CASH, 'Cash'],
        [RefundMethodEnum::CARD, 'Card'],
        [RefundMethodEnum::STORE_CREDIT, 'Store Credit'],
    ]);

    it('returns correct color for each case', function (RefundMethodEnum $case, string $expectedColor): void {
        expect($case->color())->toBe($expectedColor);
    })->with([
        [RefundMethodEnum::CASH, 'green'],
        [RefundMethodEnum::CARD, 'blue'],
        [RefundMethodEnum::STORE_CREDIT, 'purple'],
    ]);

    it('returns correct icon for each case', function (RefundMethodEnum $case, string $expectedIcon): void {
        expect($case->icon())->toBe($expectedIcon);
    })->with([
        [RefundMethodEnum::CASH, 'banknote'],
        [RefundMethodEnum::CARD, 'credit-card'],
        [RefundMethodEnum::STORE_CREDIT, 'gift'],
    ]);
});
