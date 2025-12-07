<?php

declare(strict_types=1);

use App\Enums\InvoiceStatusEnum;

it('return all invoice statuses', function (): void {
    expect(InvoiceStatusEnum::cases())->toBeArray();
});

it('invoice status label', function (): void {
    $value1 = 'Pending';
    $value2 = 'Paid';
    $value3 = 'Cancelled';
    $value4 = 'Draft';
    expect(InvoiceStatusEnum::PENDING->label())->toBe($value1)
        ->and(InvoiceStatusEnum::PAID->label())->toBe($value2)
        ->and(InvoiceStatusEnum::CANCELLED->label())->toBe($value3)
        ->and(InvoiceStatusEnum::DRAFT->label())->toBe($value4);
});

it('invoice status color', function (): void {
    $value1 = 'blue';
    $value2 = 'green';
    $value3 = 'red';
    $value4 = 'gray';
    expect(InvoiceStatusEnum::PENDING->color())->toBe($value1)
        ->and(InvoiceStatusEnum::PAID->color())->toBe($value2)
        ->and(InvoiceStatusEnum::CANCELLED->color())->toBe($value3)
        ->and(InvoiceStatusEnum::DRAFT->color())->toBe($value4);
});

it('invoice status is paid', function (): void {
    expect(InvoiceStatusEnum::PAID->isPaid())->toBeTrue()
        ->and(InvoiceStatusEnum::PENDING->isPaid())->toBeFalse()
        ->and(InvoiceStatusEnum::CANCELLED->isPaid())->toBeFalse()
        ->and(InvoiceStatusEnum::DRAFT->isPaid())->toBeFalse();
});

it('invoice status is pending', function (): void {
    expect(InvoiceStatusEnum::PENDING->isPending())->toBeTrue()
        ->and(InvoiceStatusEnum::PAID->isPending())->toBeFalse()
        ->and(InvoiceStatusEnum::CANCELLED->isPending())->toBeTrue()
        ->and(InvoiceStatusEnum::DRAFT->isPending())->toBeTrue();
});

it('invoice status to array', function (): void {
    expect(InvoiceStatusEnum::toArray())->toBeArray();
});
