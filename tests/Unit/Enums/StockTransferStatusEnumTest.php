<?php

declare(strict_types=1);

use App\Enums\StockTransferStatusEnum;

it('stock movement type to array', function (): void {
    expect(StockTransferStatusEnum::toArray())->toBeArray();
});

it('stock movement type label', function (): void {
    $value1 = 'Pending';
    $value2 = 'Completed';
    $value3 = 'Cancelled';

    expect(StockTransferStatusEnum::Pending->label())->toBe($value1)
        ->and(StockTransferStatusEnum::Completed->label())->toBe($value2)
        ->and(StockTransferStatusEnum::Cancelled->label())->toBe($value3);
});
