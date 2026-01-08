<?php

declare(strict_types=1);

use App\Enums\PaymentStatusEnum;

describe('PaymentStatusEnum', function (): void {
    it('returns all values', function (): void {
        $values = PaymentStatusEnum::values();

        expect($values)->toBe([
            'pending',
            'partial',
            'paid',
        ]);
    });

    it('returns correct label for each case', function (PaymentStatusEnum $case, string $expectedLabel): void {
        expect($case->label())->toBe($expectedLabel);
    })->with([
        [PaymentStatusEnum::PENDING, 'Pending'],
        [PaymentStatusEnum::PARTIAL, 'Partially Paid'],
        [PaymentStatusEnum::PAID, 'Paid'],
    ]);

    it('returns correct color for each case', function (PaymentStatusEnum $case, string $expectedColor): void {
        expect($case->color())->toBe($expectedColor);
    })->with([
        [PaymentStatusEnum::PENDING, 'red'],
        [PaymentStatusEnum::PARTIAL, 'yellow'],
        [PaymentStatusEnum::PAID, 'green'],
    ]);

    it('returns correct icon for each case', function (PaymentStatusEnum $case, string $expectedIcon): void {
        expect($case->icon())->toBe($expectedIcon);
    })->with([
        [PaymentStatusEnum::PENDING, 'clock'],
        [PaymentStatusEnum::PARTIAL, 'alert-circle'],
        [PaymentStatusEnum::PAID, 'check-circle'],
    ]);

    it('correctly identifies paid status', function (PaymentStatusEnum $case, bool $expectedResult): void {
        expect($case->isPaid())->toBe($expectedResult);
    })->with([
        [PaymentStatusEnum::PENDING, false],
        [PaymentStatusEnum::PARTIAL, false],
        [PaymentStatusEnum::PAID, true],
    ]);

    it('correctly identifies pending status', function (PaymentStatusEnum $case, bool $expectedResult): void {
        expect($case->isPending())->toBe($expectedResult);
    })->with([
        [PaymentStatusEnum::PENDING, true],
        [PaymentStatusEnum::PARTIAL, false],
        [PaymentStatusEnum::PAID, false],
    ]);
});
