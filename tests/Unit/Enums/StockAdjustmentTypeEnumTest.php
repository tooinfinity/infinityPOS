<?php

declare(strict_types=1);

use App\Enums\StockAdjustmentTypeEnum;

describe('StockAdjustmentTypeEnum', function (): void {
    it('returns all values', function (): void {
        $values = StockAdjustmentTypeEnum::values();

        expect($values)->toBe([
            'expired',
            'damaged',
            'manual',
            'correction',
        ]);
    });

    it('returns all options with value and label pairs', function (): void {
        $options = StockAdjustmentTypeEnum::options();

        expect($options)->toBe([
            ['value' => 'expired', 'label' => 'Expired'],
            ['value' => 'damaged', 'label' => 'Damaged'],
            ['value' => 'manual', 'label' => 'Manual Adjustment'],
            ['value' => 'correction', 'label' => 'Correction'],
        ]);
    });

    it('returns correct label for each case', function (StockAdjustmentTypeEnum $case, string $expectedLabel): void {
        expect($case->label())->toBe($expectedLabel);
    })->with([
        [StockAdjustmentTypeEnum::EXPIRED, 'Expired'],
        [StockAdjustmentTypeEnum::DAMAGED, 'Damaged'],
        [StockAdjustmentTypeEnum::MANUAL, 'Manual Adjustment'],
        [StockAdjustmentTypeEnum::CORRECTION, 'Correction'],
    ]);

    it('returns correct color for each case', function (StockAdjustmentTypeEnum $case, string $expectedColor): void {
        expect($case->color())->toBe($expectedColor);
    })->with([
        [StockAdjustmentTypeEnum::EXPIRED, 'red'],
        [StockAdjustmentTypeEnum::DAMAGED, 'orange'],
        [StockAdjustmentTypeEnum::MANUAL, 'blue'],
        [StockAdjustmentTypeEnum::CORRECTION, 'purple'],
    ]);

    it('returns correct icon for each case', function (StockAdjustmentTypeEnum $case, string $expectedIcon): void {
        expect($case->icon())->toBe($expectedIcon);
    })->with([
        [StockAdjustmentTypeEnum::EXPIRED, 'calendar-x'],
        [StockAdjustmentTypeEnum::DAMAGED, 'alert-triangle'],
        [StockAdjustmentTypeEnum::MANUAL, 'edit'],
        [StockAdjustmentTypeEnum::CORRECTION, 'check-circle'],
    ]);

    it('always requires reason for all adjustment types', function (StockAdjustmentTypeEnum $case): void {
        expect($case->requiresReason())->toBeTrue();
    })->with([
        [StockAdjustmentTypeEnum::EXPIRED],
        [StockAdjustmentTypeEnum::DAMAGED],
        [StockAdjustmentTypeEnum::MANUAL],
        [StockAdjustmentTypeEnum::CORRECTION],
    ]);

    it('correctly identifies removal adjustments', function (StockAdjustmentTypeEnum $case, bool $expectedResult): void {
        expect($case->isRemoval())->toBe($expectedResult);
    })->with([
        [StockAdjustmentTypeEnum::EXPIRED, true],
        [StockAdjustmentTypeEnum::DAMAGED, true],
        [StockAdjustmentTypeEnum::MANUAL, false],
        [StockAdjustmentTypeEnum::CORRECTION, false],
    ]);
});
