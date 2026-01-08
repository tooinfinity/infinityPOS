<?php

declare(strict_types=1);

use App\Enums\ProductUnitEnum;

describe('ProductUnitEnum', function (): void {
    it('returns all values', function (): void {
        $values = ProductUnitEnum::values();

        expect($values)->toBe([
            'piece',
            'gram',
            'milliliter',
        ]);
    });

    it('returns all options with value and label pairs', function (): void {
        $options = ProductUnitEnum::options();

        expect($options)->toBe([
            ['value' => 'piece', 'label' => 'Piece'],
            ['value' => 'gram', 'label' => 'Gram (g)'],
            ['value' => 'milliliter', 'label' => 'Milliliter (ml)'],
        ]);
    });

    it('returns correct label for each case', function (ProductUnitEnum $case, string $expectedLabel): void {
        expect($case->label())->toBe($expectedLabel);
    })->with([
        [ProductUnitEnum::PIECE, 'Piece'],
        [ProductUnitEnum::GRAM, 'Gram (g)'],
        [ProductUnitEnum::MILLILITER, 'Milliliter (ml)'],
    ]);

    it('returns correct abbreviation for each case', function (ProductUnitEnum $case, string $expectedAbbreviation): void {
        expect($case->abbreviation())->toBe($expectedAbbreviation);
    })->with([
        [ProductUnitEnum::PIECE, 'pc'],
        [ProductUnitEnum::GRAM, 'g'],
        [ProductUnitEnum::MILLILITER, 'ml'],
    ]);

    it('correctly identifies if unit requires decimal input', function (ProductUnitEnum $case, bool $expectedResult): void {
        expect($case->requiresDecimalInput())->toBe($expectedResult);
    })->with([
        [ProductUnitEnum::PIECE, false],
        [ProductUnitEnum::GRAM, true],
        [ProductUnitEnum::MILLILITER, true],
    ]);

    it('converts display value to storage unit correctly', function (ProductUnitEnum $case, float $displayValue, int $expectedStorageValue): void {
        expect($case->toStorageUnit($displayValue))->toBe($expectedStorageValue);
    })->with([
        [ProductUnitEnum::PIECE, 5.0, 5],
        [ProductUnitEnum::PIECE, 10.5, 10],
        [ProductUnitEnum::GRAM, 2.5, 2500],
        [ProductUnitEnum::GRAM, 1.0, 1000],
        [ProductUnitEnum::MILLILITER, 3.5, 3500],
        [ProductUnitEnum::MILLILITER, 0.5, 500],
    ]);

    it('converts storage value to display unit correctly', function (ProductUnitEnum $case, int $storageValue, float $expectedDisplayValue): void {
        expect($case->toDisplayUnit($storageValue))->toBe($expectedDisplayValue);
    })->with([
        [ProductUnitEnum::PIECE, 5, 5.0],
        [ProductUnitEnum::PIECE, 10, 10.0],
        [ProductUnitEnum::GRAM, 2500, 2.5],
        [ProductUnitEnum::GRAM, 1000, 1.0],
        [ProductUnitEnum::MILLILITER, 3500, 3.5],
        [ProductUnitEnum::MILLILITER, 500, 0.5],
    ]);

    it('returns correct display unit for each case', function (ProductUnitEnum $case, string $expectedDisplayUnit): void {
        expect($case->displayUnit())->toBe($expectedDisplayUnit);
    })->with([
        [ProductUnitEnum::PIECE, 'pieces'],
        [ProductUnitEnum::GRAM, 'kg'],
        [ProductUnitEnum::MILLILITER, 'L'],
    ]);
});
