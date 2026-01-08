<?php

declare(strict_types=1);

use App\Models\StockAdjustment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

test('to array', function (): void {
    $stockAdjustment = StockAdjustment::factory()->create()->refresh();

    expect(array_keys($stockAdjustment->toArray()))
        ->toBe([
            'id',
            'store_id',
            'product_id',
            'adjustment_type',
            'quantity',
            'unit_cost',
            'total_cost',
            'reason',
            'adjusted_by',
            'created_at',
        ]);
});

test('store relationship returns belongs to', function (): void {
    $stockAdjustment = new StockAdjustment();

    expect($stockAdjustment->store())
        ->toBeInstanceOf(BelongsTo::class);
});

test('product relationship returns belongs to', function (): void {
    $stockAdjustment = new StockAdjustment();

    expect($stockAdjustment->product())
        ->toBeInstanceOf(BelongsTo::class);
});

test('adjuster relationship returns belongs to', function (): void {
    $stockAdjustment = new StockAdjustment();

    expect($stockAdjustment->adjuster())
        ->toBeInstanceOf(BelongsTo::class);
});

test('is decrease returns true when quantity is negative', function (): void {
    $stockAdjustment = StockAdjustment::factory()->make(['quantity' => -10]);

    expect($stockAdjustment->isDecrease())->toBeTrue();
});

test('is decrease returns false when quantity is zero', function (): void {
    $stockAdjustment = StockAdjustment::factory()->make(['quantity' => 0]);

    expect($stockAdjustment->isDecrease())->toBeFalse();
});

test('is decrease returns false when quantity is positive', function (): void {
    $stockAdjustment = StockAdjustment::factory()->make(['quantity' => 10]);

    expect($stockAdjustment->isDecrease())->toBeFalse();
});

test('is increase returns true when quantity is positive', function (): void {
    $stockAdjustment = StockAdjustment::factory()->make(['quantity' => 10]);

    expect($stockAdjustment->isIncrease())->toBeTrue();
});

test('is increase returns false when quantity is zero', function (): void {
    $stockAdjustment = StockAdjustment::factory()->make(['quantity' => 0]);

    expect($stockAdjustment->isIncrease())->toBeFalse();
});

test('is increase returns false when quantity is negative', function (): void {
    $stockAdjustment = StockAdjustment::factory()->make(['quantity' => -10]);

    expect($stockAdjustment->isIncrease())->toBeFalse();
});

test('casts returns correct array', function (): void {
    $stockAdjustment = new StockAdjustment();

    expect($stockAdjustment->casts())
        ->toBe([
            'id' => 'integer',
            'store_id' => 'integer',
            'product_id' => 'integer',
            'adjustment_type' => App\Enums\StockAdjustmentTypeEnum::class,
            'quantity' => 'integer',
            'unit_cost' => 'integer',
            'total_cost' => 'integer',
            'reason' => 'string',
            'adjusted_by' => 'integer',
            'created_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $stockAdjustment = StockAdjustment::factory()->create()->refresh();

    expect($stockAdjustment->id)->toBeInt()
        ->and($stockAdjustment->quantity)->toBeInt()
        ->and($stockAdjustment->created_at)->toBeInstanceOf(DateTimeInterface::class);
});

test('casts adjustment_type to StockAdjustmentTypeEnum', function (): void {
    $stockAdjustment = StockAdjustment::factory()->create([
        'adjustment_type' => App\Enums\StockAdjustmentTypeEnum::MANUAL,
    ]);

    expect($stockAdjustment->adjustment_type)->toBeInstanceOf(App\Enums\StockAdjustmentTypeEnum::class)
        ->and($stockAdjustment->adjustment_type)->toBe(App\Enums\StockAdjustmentTypeEnum::MANUAL);
});

test('can set adjustment_type using enum value', function (): void {
    $stockAdjustment = StockAdjustment::factory()->create([
        'adjustment_type' => 'damaged',
    ]);

    expect($stockAdjustment->adjustment_type)->toBeInstanceOf(App\Enums\StockAdjustmentTypeEnum::class)
        ->and($stockAdjustment->adjustment_type->value)->toBe('damaged');
});

test('can access enum methods on adjustment_type', function (): void {
    $stockAdjustment = StockAdjustment::factory()->create([
        'adjustment_type' => App\Enums\StockAdjustmentTypeEnum::EXPIRED,
    ]);

    expect($stockAdjustment->adjustment_type->label())->toBe('Expired')
        ->and($stockAdjustment->adjustment_type->color())->toBeString()
        ->and($stockAdjustment->adjustment_type->icon())->toBeString()
        ->and($stockAdjustment->adjustment_type->requiresReason())->toBeTrue()
        ->and($stockAdjustment->adjustment_type->isRemoval())->toBeTrue();
});
