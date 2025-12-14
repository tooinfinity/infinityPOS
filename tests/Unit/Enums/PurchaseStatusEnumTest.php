<?php

declare(strict_types=1);

use App\Enums\PurchaseStatusEnum;

it('return all purchase statuses', function (): void {
    expect(PurchaseStatusEnum::cases())->toBeArray();
});

it('purchase status label', function (): void {
    $value1 = 'Pending';
    $value2 = 'Received';
    $value3 = 'Cancelled';

    expect(PurchaseStatusEnum::PENDING->label())->toBe($value1)
        ->and(PurchaseStatusEnum::RECEIVED->label())->toBe($value2)
        ->and(PurchaseStatusEnum::CANCELLED->label())->toBe($value3);
});

it('purchase status color', function (): void {
    $value1 = 'yellow';
    $value2 = 'green';
    $value3 = 'red';

    expect(PurchaseStatusEnum::PENDING->color())->toBe($value1)
        ->and(PurchaseStatusEnum::RECEIVED->color())->toBe($value2)
        ->and(PurchaseStatusEnum::CANCELLED->color())->toBe($value3);
});

it('purchase status to array', function (): void {
    expect(PurchaseStatusEnum::toArray())->toBeArray();
});

it('purchase status helpers', function (): void {
    expect(PurchaseStatusEnum::PENDING->isPending())->toBeTrue()
        ->and(PurchaseStatusEnum::PENDING->isCompleted())->toBeFalse()
        ->and(PurchaseStatusEnum::PENDING->isCancelled())->toBeFalse()
        ->and(PurchaseStatusEnum::RECEIVED->isCompleted())->toBeTrue()
        ->and(PurchaseStatusEnum::RECEIVED->isPending())->toBeFalse()
        ->and(PurchaseStatusEnum::RECEIVED->isCancelled())->toBeFalse()
        ->and(PurchaseStatusEnum::CANCELLED->isCancelled())->toBeTrue()
        ->and(PurchaseStatusEnum::CANCELLED->isPending())->toBeFalse()
        ->and(PurchaseStatusEnum::CANCELLED->isCompleted())->toBeFalse();
});
