<?php

declare(strict_types=1);

use App\Enums\CustomerTypeEnum;

describe('CustomerTypeEnum', function (): void {
    it('returns all values', function (): void {
        $values = CustomerTypeEnum::values();

        expect($values)->toBe([
            'walk-in',
            'regular',
            'business',
        ]);
    });

    it('returns all options with value and label pairs', function (): void {
        $options = CustomerTypeEnum::options();

        expect($options)->toBe([
            ['value' => 'walk-in', 'label' => 'Walk-in'],
            ['value' => 'regular', 'label' => 'Regular'],
            ['value' => 'business', 'label' => 'Business'],
        ]);
    });

    it('returns correct label for each case', function (CustomerTypeEnum $case, string $expectedLabel): void {
        expect($case->label())->toBe($expectedLabel);
    })->with([
        [CustomerTypeEnum::WALK_IN, 'Walk-in'],
        [CustomerTypeEnum::REGULAR, 'Regular'],
        [CustomerTypeEnum::BUSINESS, 'Business'],
    ]);

    it('returns correct color for each case', function (CustomerTypeEnum $case, string $expectedColor): void {
        expect($case->color())->toBe($expectedColor);
    })->with([
        [CustomerTypeEnum::WALK_IN, 'gray'],
        [CustomerTypeEnum::REGULAR, 'blue'],
        [CustomerTypeEnum::BUSINESS, 'purple'],
    ]);

    it('returns correct icon for each case', function (CustomerTypeEnum $case, string $expectedIcon): void {
        expect($case->icon())->toBe($expectedIcon);
    })->with([
        [CustomerTypeEnum::WALK_IN, 'user'],
        [CustomerTypeEnum::REGULAR, 'user-check'],
        [CustomerTypeEnum::BUSINESS, 'building'],
    ]);

    it('correctly identifies if customer can create invoices', function (CustomerTypeEnum $case, bool $expectedResult): void {
        expect($case->canCreateInvoices())->toBe($expectedResult);
    })->with([
        [CustomerTypeEnum::WALK_IN, false],
        [CustomerTypeEnum::REGULAR, false],
        [CustomerTypeEnum::BUSINESS, true],
    ]);

    it('correctly identifies if customer requires details', function (CustomerTypeEnum $case, bool $expectedResult): void {
        expect($case->requiresDetails())->toBe($expectedResult);
    })->with([
        [CustomerTypeEnum::WALK_IN, false],
        [CustomerTypeEnum::REGULAR, true],
        [CustomerTypeEnum::BUSINESS, true],
    ]);
});
