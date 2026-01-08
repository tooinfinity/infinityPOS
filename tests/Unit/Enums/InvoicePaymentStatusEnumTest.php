<?php

declare(strict_types=1);

use App\Enums\InvoicePaymentStatusEnum;

describe('InvoicePaymentStatusEnum', function (): void {
    it('returns all values', function (): void {
        $values = InvoicePaymentStatusEnum::values();

        expect($values)->toBe([
            'unpaid',
            'partial',
            'paid',
            'overdue',
        ]);
    });

    it('returns correct label for each case', function (InvoicePaymentStatusEnum $case, string $expectedLabel): void {
        expect($case->label())->toBe($expectedLabel);
    })->with([
        [InvoicePaymentStatusEnum::UNPAID, 'Unpaid'],
        [InvoicePaymentStatusEnum::PARTIAL, 'Partially Paid'],
        [InvoicePaymentStatusEnum::PAID, 'Paid'],
        [InvoicePaymentStatusEnum::OVERDUE, 'Overdue'],
    ]);

    it('returns correct color for each case', function (InvoicePaymentStatusEnum $case, string $expectedColor): void {
        expect($case->color())->toBe($expectedColor);
    })->with([
        [InvoicePaymentStatusEnum::UNPAID, 'gray'],
        [InvoicePaymentStatusEnum::PARTIAL, 'yellow'],
        [InvoicePaymentStatusEnum::PAID, 'green'],
        [InvoicePaymentStatusEnum::OVERDUE, 'red'],
    ]);

    it('returns correct icon for each case', function (InvoicePaymentStatusEnum $case, string $expectedIcon): void {
        expect($case->icon())->toBe($expectedIcon);
    })->with([
        [InvoicePaymentStatusEnum::UNPAID, 'circle'],
        [InvoicePaymentStatusEnum::PARTIAL, 'alert-circle'],
        [InvoicePaymentStatusEnum::PAID, 'check-circle-2'],
        [InvoicePaymentStatusEnum::OVERDUE, 'alert-triangle'],
    ]);

    it('correctly identifies paid status', function (InvoicePaymentStatusEnum $case, bool $expectedResult): void {
        expect($case->isPaid())->toBe($expectedResult);
    })->with([
        [InvoicePaymentStatusEnum::UNPAID, false],
        [InvoicePaymentStatusEnum::PARTIAL, false],
        [InvoicePaymentStatusEnum::PAID, true],
        [InvoicePaymentStatusEnum::OVERDUE, false],
    ]);

    it('correctly identifies if status requires action', function (InvoicePaymentStatusEnum $case, bool $expectedResult): void {
        expect($case->requiresAction())->toBe($expectedResult);
    })->with([
        [InvoicePaymentStatusEnum::UNPAID, true],
        [InvoicePaymentStatusEnum::PARTIAL, true],
        [InvoicePaymentStatusEnum::PAID, false],
        [InvoicePaymentStatusEnum::OVERDUE, true],
    ]);
});
