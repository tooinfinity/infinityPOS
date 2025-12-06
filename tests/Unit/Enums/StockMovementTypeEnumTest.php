<?php

declare(strict_types=1);

use App\Enums\StockMovementTypeEnum;

it('return all stock movement types', function (): void {
    expect(StockMovementTypeEnum::cases())->toBeArray();
});

it('stock movement type label', function (): void {
    $value1 = 'Purchase';
    $value2 = 'Sale';
    $value3 = 'Sale Return';
    $value4 = 'Purchase Return';
    $value5 = 'Adjustment';
    $value6 = 'Transfer';

    expect(StockMovementTypeEnum::PURCHASE->label())->toBe($value1)
        ->and(StockMovementTypeEnum::SALE->label())->toBe($value2)
        ->and(StockMovementTypeEnum::SALE_RETURN->label())->toBe($value3)
        ->and(StockMovementTypeEnum::PURCHASE_RETURN->label())->toBe($value4)
        ->and(StockMovementTypeEnum::ADJUSTMENT->label())->toBe($value5)
        ->and(StockMovementTypeEnum::TRANSFER->label())->toBe($value6);
});

it('stock movement type color', function (): void {
    $value1 = 'blue';
    $value2 = 'green';
    $value3 = 'orange';
    $value4 = 'red';
    $value5 = 'purple';
    $value6 = 'indigo';

    expect(StockMovementTypeEnum::PURCHASE->color())->toBe($value1)
        ->and(StockMovementTypeEnum::SALE->color())->toBe($value2)
        ->and(StockMovementTypeEnum::SALE_RETURN->color())->toBe($value3)
        ->and(StockMovementTypeEnum::PURCHASE_RETURN->color())->toBe($value4)
        ->and(StockMovementTypeEnum::ADJUSTMENT->color())->toBe($value5)
        ->and(StockMovementTypeEnum::TRANSFER->color())->toBe($value6);
});

it('stock movement type is incoming', function (): void {
    expect(StockMovementTypeEnum::PURCHASE->isIncoming())->toBeTrue()
        ->and(StockMovementTypeEnum::SALE->isIncoming())->toBeFalse()
        ->and(StockMovementTypeEnum::SALE_RETURN->isIncoming())->toBeTrue()
        ->and(StockMovementTypeEnum::PURCHASE_RETURN->isIncoming())->toBeFalse()
        ->and(StockMovementTypeEnum::ADJUSTMENT->isIncoming())->toBeFalse()
        ->and(StockMovementTypeEnum::TRANSFER->isIncoming())->toBeFalse();
});

it('stock movement type to array', function (): void {
    expect(StockMovementTypeEnum::toArray())->toBeArray();
});
