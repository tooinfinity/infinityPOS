<?php

declare(strict_types=1);

use App\Enums\SaleStatusEnum;

it('sale status to array', function (): void {
    expect(SaleStatusEnum::toArray())->toBeArray();
});

it('sale status label', function (): void {
    $value1 = 'Pending';
    $value2 = 'Completed';
    $value3 = 'Cancelled';

    expect(SaleStatusEnum::Pending->label())->toBe($value1)
        ->and(SaleStatusEnum::Completed->label())->toBe($value2)
        ->and(SaleStatusEnum::Cancelled->label())->toBe($value3);
});

it('validates state transitions for sale status', function (): void {
    expect(SaleStatusEnum::Pending->canTransitionTo(SaleStatusEnum::Completed))->toBeTrue()
        ->and(SaleStatusEnum::Pending->canTransitionTo(SaleStatusEnum::Cancelled))->toBeTrue()
        ->and(SaleStatusEnum::Pending->canTransitionTo(SaleStatusEnum::Pending))->toBeFalse()
        ->and(SaleStatusEnum::Completed->canTransitionTo(SaleStatusEnum::Pending))->toBeFalse()
        ->and(SaleStatusEnum::Completed->canTransitionTo(SaleStatusEnum::Cancelled))->toBeTrue()
        ->and(SaleStatusEnum::Completed->canTransitionTo(SaleStatusEnum::Completed))->toBeFalse()
        ->and(SaleStatusEnum::Cancelled->canTransitionTo(SaleStatusEnum::Pending))->toBeFalse()
        ->and(SaleStatusEnum::Cancelled->canTransitionTo(SaleStatusEnum::Completed))->toBeFalse()
        ->and(SaleStatusEnum::Cancelled->canTransitionTo(SaleStatusEnum::Cancelled))->toBeFalse();
});

it('returns valid transitions for sale status', function (): void {
    expect(SaleStatusEnum::Pending->getValidTransitions())->toHaveCount(2)
        ->toContain(SaleStatusEnum::Completed)
        ->toContain(SaleStatusEnum::Cancelled)
        ->and(SaleStatusEnum::Completed->getValidTransitions())->toHaveCount(1)
        ->toContain(SaleStatusEnum::Cancelled)
        ->and(SaleStatusEnum::Cancelled->getValidTransitions())->toHaveCount(0)
        ->toBe([]);
});
