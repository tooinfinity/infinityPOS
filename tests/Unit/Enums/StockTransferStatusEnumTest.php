<?php

declare(strict_types=1);

use App\Enums\StockTransferStatusEnum;

it('stock transfer status to array', function (): void {
    expect(StockTransferStatusEnum::toArray())->toBeArray();
});

it('stock transfer status label', function (): void {
    $value1 = 'Pending';
    $value2 = 'Completed';
    $value3 = 'Cancelled';

    expect(StockTransferStatusEnum::Pending->label())->toBe($value1)
        ->and(StockTransferStatusEnum::Completed->label())->toBe($value2)
        ->and(StockTransferStatusEnum::Cancelled->label())->toBe($value3);
});

it('validates state transitions for stock transfer status', function (): void {
    expect(StockTransferStatusEnum::Pending->canTransitionTo(StockTransferStatusEnum::Completed))->toBeTrue()
        ->and(StockTransferStatusEnum::Pending->canTransitionTo(StockTransferStatusEnum::Cancelled))->toBeTrue()
        ->and(StockTransferStatusEnum::Pending->canTransitionTo(StockTransferStatusEnum::Pending))->toBeFalse()
        ->and(StockTransferStatusEnum::Completed->canTransitionTo(StockTransferStatusEnum::Pending))->toBeFalse()
        ->and(StockTransferStatusEnum::Completed->canTransitionTo(StockTransferStatusEnum::Cancelled))->toBeFalse()
        ->and(StockTransferStatusEnum::Completed->canTransitionTo(StockTransferStatusEnum::Completed))->toBeFalse()
        ->and(StockTransferStatusEnum::Cancelled->canTransitionTo(StockTransferStatusEnum::Pending))->toBeFalse()
        ->and(StockTransferStatusEnum::Cancelled->canTransitionTo(StockTransferStatusEnum::Completed))->toBeFalse()
        ->and(StockTransferStatusEnum::Cancelled->canTransitionTo(StockTransferStatusEnum::Cancelled))->toBeFalse();
});

it('returns valid transitions for stock transfer status', function (): void {
    expect(StockTransferStatusEnum::Pending->getValidTransitions())->toHaveCount(2)
        ->toContain(StockTransferStatusEnum::Completed)
        ->toContain(StockTransferStatusEnum::Cancelled)
        ->and(StockTransferStatusEnum::Completed->getValidTransitions())->toHaveCount(0)
        ->toBe([])
        ->and(StockTransferStatusEnum::Cancelled->getValidTransitions())->toHaveCount(0)
        ->toBe([]);

});
