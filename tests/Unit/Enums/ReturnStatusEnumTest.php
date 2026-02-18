<?php

declare(strict_types=1);

use App\Enums\ReturnStatusEnum;

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
