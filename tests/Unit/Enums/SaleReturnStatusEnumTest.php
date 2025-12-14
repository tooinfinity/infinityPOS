<?php

declare(strict_types=1);

use App\Enums\SaleReturnStatusEnum;

it('return all sale return statuses', function (): void {
    expect(SaleReturnStatusEnum::cases())->toBeArray();
});

it('sale return status label', function (): void {
    $value1 = 'Pending';
    $value2 = 'Completed';
    $value3 = 'Cancelled';

    expect(SaleReturnStatusEnum::PENDING->label())->toBe($value1)
        ->and(SaleReturnStatusEnum::COMPLETED->label())->toBe($value2)
        ->and(SaleReturnStatusEnum::CANCELLED->label())->toBe($value3);
});

it('sale return status color', function (): void {
    $value1 = 'yellow';
    $value2 = 'green';
    $value3 = 'red';

    expect(SaleReturnStatusEnum::PENDING->color())->toBe($value1)
        ->and(SaleReturnStatusEnum::COMPLETED->color())->toBe($value2)
        ->and(SaleReturnStatusEnum::CANCELLED->color())->toBe($value3);
});

it('sale return status to array', function (): void {
    expect(SaleReturnStatusEnum::toArray())->toBeArray();
});

it('sale return status helpers', function (): void {
    expect(SaleReturnStatusEnum::PENDING->isPending())->toBeTrue()
        ->and(SaleReturnStatusEnum::PENDING->isCompleted())->toBeFalse()
        ->and(SaleReturnStatusEnum::PENDING->isCancelled())->toBeFalse()
        ->and(SaleReturnStatusEnum::COMPLETED->isCompleted())->toBeTrue()
        ->and(SaleReturnStatusEnum::COMPLETED->isPending())->toBeFalse()
        ->and(SaleReturnStatusEnum::COMPLETED->isCancelled())->toBeFalse()
        ->and(SaleReturnStatusEnum::CANCELLED->isCancelled())->toBeTrue()
        ->and(SaleReturnStatusEnum::CANCELLED->isPending())->toBeFalse()
        ->and(SaleReturnStatusEnum::CANCELLED->isCompleted())->toBeFalse();
});
