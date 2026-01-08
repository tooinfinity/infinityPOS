<?php

declare(strict_types=1);

use App\DTOs\StockAdjustmentData;
use App\Enums\StockAdjustmentTypeEnum;
use Illuminate\Validation\ValidationException;

it('creates stock adjustment DTO from array', function (): void {
    $data = StockAdjustmentData::from([
        'store_id' => 1,
        'product_id' => 10,
        'adjustment_type' => 'manual',
        'quantity' => 50,
        'unit_cost' => 100,
        'total_cost' => 5000,
        'reason' => 'New stock arrival',
        'adjusted_by' => 5,
    ]);

    expect($data->storeId)->toBe(1)
        ->and($data->productId)->toBe(10)
        ->and($data->adjustmentType)->toBeInstanceOf(StockAdjustmentTypeEnum::class)
        ->and($data->adjustmentType)->toBe(StockAdjustmentTypeEnum::MANUAL)
        ->and($data->quantity)->toBe(50)
        ->and($data->unitCost)->toBe(100)
        ->and($data->totalCost)->toBe(5000)
        ->and($data->reason)->toBe('New stock arrival')
        ->and($data->adjustedBy)->toBe(5);
});

it('creates stock adjustment with minimal data', function (): void {
    $data = StockAdjustmentData::from([
        'store_id' => 1,
        'product_id' => 10,
        'adjustment_type' => 'damaged',
        'quantity' => -10,
        'reason' => 'Damaged goods',
    ]);

    expect($data->adjustmentType)->toBe(StockAdjustmentTypeEnum::DAMAGED)
        ->and($data->unitCost)->toBeNull()
        ->and($data->totalCost)->toBeNull()
        ->and($data->adjustedBy)->toBeNull();
});

it('calculates total cost from unit cost and quantity', function (): void {
    $data = StockAdjustmentData::from([
        'store_id' => 1,
        'product_id' => 10,
        'adjustment_type' => 'correction',
        'quantity' => -5,
        'unit_cost' => 120,
        'reason' => 'Correction',
    ]);

    expect($data->calculatedTotalCost())->toBe(600); // abs(-5) * 120
});

it('returns provided total cost when available', function (): void {
    $data = StockAdjustmentData::from([
        'store_id' => 1,
        'product_id' => 10,
        'adjustment_type' => 'manual',
        'quantity' => 10,
        'unit_cost' => 100,
        'total_cost' => 950,
        'reason' => 'Discounted purchase',
    ]);

    expect($data->calculatedTotalCost())->toBe(950);
});

it('returns null when no cost information available', function (): void {
    $data = StockAdjustmentData::from([
        'store_id' => 1,
        'product_id' => 10,
        'adjustment_type' => 'expired',
        'quantity' => -20,
        'reason' => 'Expired products',
    ]);

    expect($data->calculatedTotalCost())->toBeNull();
});

it('validates adjustment type with enum', function (): void {
    $data = StockAdjustmentData::from([
        'store_id' => 1,
        'product_id' => 10,
        'adjustment_type' => 'damaged',
        'quantity' => -5,
        'reason' => 'Test',
    ]);

    expect($data->adjustmentType)->toBeInstanceOf(StockAdjustmentTypeEnum::class)
        ->and($data->adjustmentType)->toBe(StockAdjustmentTypeEnum::DAMAGED);
});

it('accepts enum case values for adjustment type', function (): void {
    $data = StockAdjustmentData::from([
        'store_id' => 1,
        'product_id' => 10,
        'adjustment_type' => StockAdjustmentTypeEnum::EXPIRED->value,
        'quantity' => -10,
        'reason' => 'Expired items',
    ]);

    expect($data->adjustmentType)->toBe(StockAdjustmentTypeEnum::EXPIRED);
});

it('can use enum directly for adjustment type', function (): void {
    $data = StockAdjustmentData::from([
        'store_id' => 1,
        'product_id' => 10,
        'adjustment_type' => StockAdjustmentTypeEnum::CORRECTION,
        'quantity' => 5,
        'reason' => 'Stock correction',
    ]);

    expect($data->adjustmentType)->toBe(StockAdjustmentTypeEnum::CORRECTION);
});

it('rejects invalid adjustment type', function (): void {
    StockAdjustmentData::validateAndCreate([
        'store_id' => 1,
        'product_id' => 10,
        'adjustment_type' => 'invalid',
        'quantity' => 10,
        'reason' => 'Test',
    ]);
})->throws(ValidationException::class);

it('validates required fields', function (): void {
    StockAdjustmentData::validateAndCreate([
        'store_id' => 1,
        'product_id' => 10,
    ]);
})->throws(ValidationException::class);

it('handles snake_case mapping', function (): void {
    $data = StockAdjustmentData::from([
        'store_id' => 2,
        'product_id' => 15,
        'adjustment_type' => 'manual',
        'quantity' => -3,
        'unit_cost' => 200,
        'total_cost' => 600,
        'reason' => 'Manual adjustment',
        'adjusted_by' => 8,
    ]);

    expect($data->storeId)->toBe(2)
        ->and($data->productId)->toBe(15)
        ->and($data->adjustmentType)->toBe(StockAdjustmentTypeEnum::MANUAL)
        ->and($data->unitCost)->toBe(200)
        ->and($data->totalCost)->toBe(600)
        ->and($data->adjustedBy)->toBe(8);
});
