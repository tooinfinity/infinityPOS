<?php

declare(strict_types=1);

use App\Enums\HasStatusTransitions;
use App\Enums\PurchaseStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Enums\StockTransferStatusEnum;

it('purchase status to array', function (): void {
    expect(PurchaseStatusEnum::toArray())->toBeArray();
});

it('purchase status label', function (): void {
    $value1 = 'Pending';
    $value2 = 'Ordered';
    $value3 = 'Received';
    $value4 = 'Cancelled';

    expect(PurchaseStatusEnum::Pending->label())->toBe($value1)
        ->and(PurchaseStatusEnum::Ordered->label())->toBe($value2)
        ->and(PurchaseStatusEnum::Received->label())->toBe($value3)
        ->and(PurchaseStatusEnum::Cancelled->label())->toBe($value4);
});

it('validates state transitions for purchase status', function (): void {
    // Pending can transition to Ordered, Received, and Cancelled
    expect(PurchaseStatusEnum::Pending->canTransitionTo(PurchaseStatusEnum::Ordered))->toBeTrue()
        ->and(PurchaseStatusEnum::Pending->canTransitionTo(PurchaseStatusEnum::Received))->toBeTrue()
        ->and(PurchaseStatusEnum::Pending->canTransitionTo(PurchaseStatusEnum::Cancelled))->toBeTrue()
        ->and(PurchaseStatusEnum::Pending->canTransitionTo(PurchaseStatusEnum::Pending))->toBeFalse()
        ->and(PurchaseStatusEnum::Ordered->canTransitionTo(PurchaseStatusEnum::Received))->toBeTrue()
        ->and(PurchaseStatusEnum::Ordered->canTransitionTo(PurchaseStatusEnum::Cancelled))->toBeTrue()
        ->and(PurchaseStatusEnum::Ordered->canTransitionTo(PurchaseStatusEnum::Ordered))->toBeFalse()
        ->and(PurchaseStatusEnum::Ordered->canTransitionTo(PurchaseStatusEnum::Pending))->toBeFalse()
        ->and(PurchaseStatusEnum::Received->canTransitionTo(PurchaseStatusEnum::Pending))->toBeFalse()
        ->and(PurchaseStatusEnum::Received->canTransitionTo(PurchaseStatusEnum::Ordered))->toBeFalse()
        ->and(PurchaseStatusEnum::Received->canTransitionTo(PurchaseStatusEnum::Cancelled))->toBeFalse()
        ->and(PurchaseStatusEnum::Received->canTransitionTo(PurchaseStatusEnum::Received))->toBeFalse()
        ->and(PurchaseStatusEnum::Cancelled->canTransitionTo(PurchaseStatusEnum::Pending))->toBeTrue()
        ->and(PurchaseStatusEnum::Cancelled->canTransitionTo(PurchaseStatusEnum::Ordered))->toBeFalse()
        ->and(PurchaseStatusEnum::Cancelled->canTransitionTo(PurchaseStatusEnum::Received))->toBeFalse()
        ->and(PurchaseStatusEnum::Cancelled->canTransitionTo(PurchaseStatusEnum::Cancelled))->toBeFalse();
});

it('returns valid transitions for purchase status', function (): void {
    expect(PurchaseStatusEnum::Pending->getValidTransitions())->toHaveCount(3)
        ->toContain(PurchaseStatusEnum::Ordered)
        ->toContain(PurchaseStatusEnum::Received)
        ->toContain(PurchaseStatusEnum::Cancelled)
        ->and(PurchaseStatusEnum::Ordered->getValidTransitions())->toHaveCount(2)
        ->toContain(PurchaseStatusEnum::Received)
        ->toContain(PurchaseStatusEnum::Cancelled)
        ->and(PurchaseStatusEnum::Received->getValidTransitions())->toHaveCount(0)
        ->toBe([])
        ->and(PurchaseStatusEnum::Cancelled->getValidTransitions())->toHaveCount(1)
        ->toContain(PurchaseStatusEnum::Pending);
});

it('canTransitionTo returns false for different enum type', function (): void {
    expect(PurchaseStatusEnum::Pending->canTransitionTo(SaleStatusEnum::Pending))->toBeFalse()
        ->and(PurchaseStatusEnum::Pending->canTransitionTo(StockTransferStatusEnum::Pending))->toBeFalse()
        ->and(PurchaseStatusEnum::Pending->canTransitionTo(ReturnStatusEnum::Pending))->toBeFalse()
        ->and(PurchaseStatusEnum::Received->canTransitionTo(SaleStatusEnum::Completed))->toBeFalse()
        ->and(PurchaseStatusEnum::Cancelled->canTransitionTo(StockTransferStatusEnum::Cancelled))->toBeFalse();
});

it('implements HasStatusTransitions interface', function (): void {
    $enum = PurchaseStatusEnum::Pending;
    expect($enum)->toBeInstanceOf(HasStatusTransitions::class);
});
