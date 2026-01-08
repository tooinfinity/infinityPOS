<?php

declare(strict_types=1);

use App\Enums\PurchaseStatusEnum;

describe('PurchaseStatusEnum', function (): void {
    it('returns all values', function (): void {
        $values = PurchaseStatusEnum::values();

        expect($values)->toBe([
            'pending',
            'completed',
            'cancelled',
        ]);
    });

    it('returns all options with value and label pairs', function (): void {
        $options = PurchaseStatusEnum::options();

        expect($options)->toBe([
            ['value' => 'pending', 'label' => 'Pending'],
            ['value' => 'completed', 'label' => 'Completed'],
            ['value' => 'cancelled', 'label' => 'Cancelled'],
        ]);
    });

    it('returns correct label for each case', function (PurchaseStatusEnum $case, string $expectedLabel): void {
        expect($case->label())->toBe($expectedLabel);
    })->with([
        [PurchaseStatusEnum::PENDING, 'Pending'],
        [PurchaseStatusEnum::COMPLETED, 'Completed'],
        [PurchaseStatusEnum::CANCELLED, 'Cancelled'],
    ]);

    it('returns correct color for each case', function (PurchaseStatusEnum $case, string $expectedColor): void {
        expect($case->color())->toBe($expectedColor);
    })->with([
        [PurchaseStatusEnum::PENDING, 'yellow'],
        [PurchaseStatusEnum::COMPLETED, 'green'],
        [PurchaseStatusEnum::CANCELLED, 'red'],
    ]);

    it('returns correct icon for each case', function (PurchaseStatusEnum $case, string $expectedIcon): void {
        expect($case->icon())->toBe($expectedIcon);
    })->with([
        [PurchaseStatusEnum::PENDING, 'clock'],
        [PurchaseStatusEnum::COMPLETED, 'check-circle'],
        [PurchaseStatusEnum::CANCELLED, 'x-circle'],
    ]);

    it('correctly identifies completed status', function (PurchaseStatusEnum $case, bool $expectedResult): void {
        expect($case->isCompleted())->toBe($expectedResult);
    })->with([
        [PurchaseStatusEnum::PENDING, false],
        [PurchaseStatusEnum::COMPLETED, true],
        [PurchaseStatusEnum::CANCELLED, false],
    ]);

    it('correctly identifies if status can be modified', function (PurchaseStatusEnum $case, bool $expectedResult): void {
        expect($case->canBeModified())->toBe($expectedResult);
    })->with([
        [PurchaseStatusEnum::PENDING, true],
        [PurchaseStatusEnum::COMPLETED, false],
        [PurchaseStatusEnum::CANCELLED, false],
    ]);
});
