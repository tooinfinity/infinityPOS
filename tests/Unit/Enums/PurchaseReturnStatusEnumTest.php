<?php

declare(strict_types=1);

use App\Enums\PurchaseReturnStatusEnum;

it('return all purchase return statuses', function (): void {
    expect(PurchaseReturnStatusEnum::cases())->toBeArray();
});

it('purchase return status label', function (): void {
    $value1 = 'Pending';
    $value2 = 'Completed';
    $value3 = 'Cancelled';

    expect(PurchaseReturnStatusEnum::PENDING->label())->toBe($value1)
        ->and(PurchaseReturnStatusEnum::COMPLETED->label())->toBe($value2)
        ->and(PurchaseReturnStatusEnum::CANCELLED->label())->toBe($value3);
});

it('purchase return status color', function (): void {
    $value1 = 'yellow';
    $value2 = 'green';
    $value3 = 'red';

    expect(PurchaseReturnStatusEnum::PENDING->color())->toBe($value1)
        ->and(PurchaseReturnStatusEnum::COMPLETED->color())->toBe($value2)
        ->and(PurchaseReturnStatusEnum::CANCELLED->color())->toBe($value3);
});

it('purchase return status to array', function (): void {
    expect(PurchaseReturnStatusEnum::toArray())->toBeArray();
});

it('purchase return status helpers', function (): void {
    expect(PurchaseReturnStatusEnum::PENDING->isPending())->toBeTrue()
        ->and(PurchaseReturnStatusEnum::PENDING->isCompleted())->toBeFalse()
        ->and(PurchaseReturnStatusEnum::PENDING->isCancelled())->toBeFalse()
        ->and(PurchaseReturnStatusEnum::COMPLETED->isCompleted())->toBeTrue()
        ->and(PurchaseReturnStatusEnum::COMPLETED->isPending())->toBeFalse()
        ->and(PurchaseReturnStatusEnum::COMPLETED->isCancelled())->toBeFalse()
        ->and(PurchaseReturnStatusEnum::CANCELLED->isCancelled())->toBeTrue()
        ->and(PurchaseReturnStatusEnum::CANCELLED->isPending())->toBeFalse()
        ->and(PurchaseReturnStatusEnum::CANCELLED->isCompleted())->toBeFalse();
});
