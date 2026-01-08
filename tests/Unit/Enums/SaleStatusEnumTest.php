<?php

declare(strict_types=1);

use App\Enums\SaleStatusEnum;

describe('SaleStatusEnum', function (): void {
    it('returns all values', function (): void {
        $values = SaleStatusEnum::values();

        expect($values)->toBe([
            'completed',
            'pending',
            'returned',
        ]);
    });

    it('returns correct label for each case', function (SaleStatusEnum $case, string $expectedLabel): void {
        expect($case->label())->toBe($expectedLabel);
    })->with([
        [SaleStatusEnum::COMPLETED, 'Completed'],
        [SaleStatusEnum::PENDING, 'Pending'],
        [SaleStatusEnum::RETURNED, 'Returned'],
    ]);

    it('returns correct color for each case', function (SaleStatusEnum $case, string $expectedColor): void {
        expect($case->color())->toBe($expectedColor);
    })->with([
        [SaleStatusEnum::COMPLETED, 'green'],
        [SaleStatusEnum::PENDING, 'yellow'],
        [SaleStatusEnum::RETURNED, 'red'],
    ]);

    it('returns correct icon for each case', function (SaleStatusEnum $case, string $expectedIcon): void {
        expect($case->icon())->toBe($expectedIcon);
    })->with([
        [SaleStatusEnum::COMPLETED, 'check-circle'],
        [SaleStatusEnum::PENDING, 'clock'],
        [SaleStatusEnum::RETURNED, 'rotate-ccw'],
    ]);

    it('correctly identifies completed status', function (SaleStatusEnum $case, bool $expectedResult): void {
        expect($case->isCompleted())->toBe($expectedResult);
    })->with([
        [SaleStatusEnum::COMPLETED, true],
        [SaleStatusEnum::PENDING, false],
        [SaleStatusEnum::RETURNED, false],
    ]);

    it('correctly identifies if sale can be returned', function (SaleStatusEnum $case, bool $expectedResult): void {
        expect($case->canBeReturned())->toBe($expectedResult);
    })->with([
        [SaleStatusEnum::COMPLETED, true],
        [SaleStatusEnum::PENDING, false],
        [SaleStatusEnum::RETURNED, false],
    ]);
});
