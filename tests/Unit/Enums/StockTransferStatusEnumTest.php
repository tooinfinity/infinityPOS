<?php

declare(strict_types=1);

use App\Enums\StockTransferStatusEnum;

it('return all stock transfer statuses', function (): void {
    expect(StockTransferStatusEnum::cases())->toBeArray();
});

it('stock transfer status label', function (): void {
    $value1 = 'Pending';
    $value2 = 'Completed';
    $value3 = 'Cancelled';

    expect(StockTransferStatusEnum::PENDING->label())->toBe($value1)
        ->and(StockTransferStatusEnum::COMPLETED->label())->toBe($value2)
        ->and(StockTransferStatusEnum::CANCELLED->label())->toBe($value3);
});

it('stock transfer status color', function (): void {
    $value1 = 'yellow';
    $value2 = 'green';
    $value3 = 'red';

    expect(StockTransferStatusEnum::PENDING->color())->toBe($value1)
        ->and(StockTransferStatusEnum::COMPLETED->color())->toBe($value2)
        ->and(StockTransferStatusEnum::CANCELLED->color())->toBe($value3);
});

it('stock transfer status to array', function (): void {
    expect(StockTransferStatusEnum::toArray())->toBeArray();
});
