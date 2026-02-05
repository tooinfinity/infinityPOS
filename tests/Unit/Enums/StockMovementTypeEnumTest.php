<?php

declare(strict_types=1);

use App\Enums\StockMovementTypeEnum;

it('stock movement type to array', function (): void {
    expect(StockMovementTypeEnum::toArray())->toBeArray();
});

it('stock movement type label', function (): void {
    $value1 = 'In';
    $value2 = 'Out';
    $value3 = 'Adjustment';
    $value4 = 'Transfer';

    expect(StockMovementTypeEnum::In->label())->toBe($value1)
        ->and(StockMovementTypeEnum::Out->label())->toBe($value2)
        ->and(StockMovementTypeEnum::Adjustment->label())->toBe($value3)
        ->and(StockMovementTypeEnum::Transfer->label())->toBe($value4);
});
