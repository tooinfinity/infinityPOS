<?php

declare(strict_types=1);

use App\Enums\SaleStatusEnum;

it('return all sale statuses', function (): void {
    expect(SaleStatusEnum::cases())->toBeArray();
});

it('sale status label', function (): void {
    $value1 = 'Pending';
    $value2 = 'Completed';
    $value3 = 'Cancelled';

    expect(SaleStatusEnum::PENDING->label())->toBe($value1)
        ->and(SaleStatusEnum::COMPLETED->label())->toBe($value2)
        ->and(SaleStatusEnum::CANCELLED->label())->toBe($value3);
});

it('sale status color', function (): void {
    $value1 = 'yellow';
    $value2 = 'green';
    $value3 = 'red';

    expect(SaleStatusEnum::PENDING->color())->toBe($value1)
        ->and(SaleStatusEnum::COMPLETED->color())->toBe($value2)
        ->and(SaleStatusEnum::CANCELLED->color())->toBe($value3);
});

it('sale status to array', function (): void {
    expect(SaleStatusEnum::toArray())->toBeArray();
});

it('sale status helpers', function (): void {
    expect(SaleStatusEnum::PENDING->isPending())->toBeTrue()
        ->and(SaleStatusEnum::PENDING->isCompleted())->toBeFalse()
        ->and(SaleStatusEnum::PENDING->isCancelled())->toBeFalse()
        ->and(SaleStatusEnum::COMPLETED->isCompleted())->toBeTrue()
        ->and(SaleStatusEnum::COMPLETED->isPending())->toBeFalse()
        ->and(SaleStatusEnum::COMPLETED->isCancelled())->toBeFalse()
        ->and(SaleStatusEnum::CANCELLED->isCancelled())->toBeTrue()
        ->and(SaleStatusEnum::CANCELLED->isPending())->toBeFalse()
        ->and(SaleStatusEnum::CANCELLED->isCompleted())->toBeFalse();
});
