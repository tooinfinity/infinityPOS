<?php

declare(strict_types=1);

use App\Enums\ExpenseCategoryEnum;

describe('ExpenseCategoryEnum', function (): void {
    it('returns all values', function (): void {
        $values = ExpenseCategoryEnum::values();

        expect($values)->toBe([
            'utilities',
            'supplies',
            'maintenance',
            'other',
        ]);
    });

    it('returns all options with value and label pairs', function (): void {
        $options = ExpenseCategoryEnum::options();

        expect($options)->toBe([
            ['value' => 'utilities', 'label' => 'Utilities'],
            ['value' => 'supplies', 'label' => 'Supplies'],
            ['value' => 'maintenance', 'label' => 'Maintenance'],
            ['value' => 'other', 'label' => 'Other'],
        ]);
    });

    it('returns correct label for each case', function (ExpenseCategoryEnum $case, string $expectedLabel): void {
        expect($case->label())->toBe($expectedLabel);
    })->with([
        [ExpenseCategoryEnum::UTILITIES, 'Utilities'],
        [ExpenseCategoryEnum::SUPPLIES, 'Supplies'],
        [ExpenseCategoryEnum::MAINTENANCE, 'Maintenance'],
        [ExpenseCategoryEnum::OTHER, 'Other'],
    ]);

    it('returns correct color for each case', function (ExpenseCategoryEnum $case, string $expectedColor): void {
        expect($case->color())->toBe($expectedColor);
    })->with([
        [ExpenseCategoryEnum::UTILITIES, 'blue'],
        [ExpenseCategoryEnum::SUPPLIES, 'green'],
        [ExpenseCategoryEnum::MAINTENANCE, 'orange'],
        [ExpenseCategoryEnum::OTHER, 'gray'],
    ]);

    it('returns correct icon for each case', function (ExpenseCategoryEnum $case, string $expectedIcon): void {
        expect($case->icon())->toBe($expectedIcon);
    })->with([
        [ExpenseCategoryEnum::UTILITIES, 'zap'],
        [ExpenseCategoryEnum::SUPPLIES, 'package'],
        [ExpenseCategoryEnum::MAINTENANCE, 'wrench'],
        [ExpenseCategoryEnum::OTHER, 'more-horizontal'],
    ]);
});
