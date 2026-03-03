<?php

declare(strict_types=1);

use App\Enums\HasStatusTransitions;
use App\Enums\PurchaseStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Enums\StockTransferStatusEnum;

it('sale status to array', function (): void {
    expect(ReturnStatusEnum::toArray())->toBeArray();
});

it('return status label', function (): void {
    $value1 = 'Pending';
    $value2 = 'Completed';

    expect(ReturnStatusEnum::Pending->label())->toBe($value1)
        ->and(ReturnStatusEnum::Completed->label())->toBe($value2);
});

it('validates state transitions for return status', function (): void {
    // Pending can transition to Completed only
    expect(ReturnStatusEnum::Pending->canTransitionTo(ReturnStatusEnum::Completed))->toBeTrue()
        ->and(ReturnStatusEnum::Pending->canTransitionTo(ReturnStatusEnum::Pending))->toBeFalse()
        ->and(ReturnStatusEnum::Completed->canTransitionTo(ReturnStatusEnum::Pending))->toBeFalse()
        ->and(ReturnStatusEnum::Completed->canTransitionTo(ReturnStatusEnum::Completed))->toBeFalse();
});

it('returns valid transitions for return status', function (): void {
    expect(ReturnStatusEnum::Pending->getValidTransitions())->toHaveCount(1)
        ->toContain(ReturnStatusEnum::Completed)
        ->and(ReturnStatusEnum::Completed->getValidTransitions())->toHaveCount(0)
        ->toBe([]);
});

it('canTransitionTo returns false for different enum type', function (): void {
    expect(ReturnStatusEnum::Pending->canTransitionTo(SaleStatusEnum::Pending))->toBeFalse()
        ->and(ReturnStatusEnum::Pending->canTransitionTo(PurchaseStatusEnum::Pending))->toBeFalse()
        ->and(ReturnStatusEnum::Pending->canTransitionTo(StockTransferStatusEnum::Pending))->toBeFalse()
        ->and(ReturnStatusEnum::Completed->canTransitionTo(SaleStatusEnum::Completed))->toBeFalse()
        ->and(ReturnStatusEnum::Completed->canTransitionTo(PurchaseStatusEnum::Received))->toBeFalse();
});

it('implements HasStatusTransitions interface', function (): void {
    $enum = ReturnStatusEnum::Pending;
    expect($enum)->toBeInstanceOf(HasStatusTransitions::class);
});
