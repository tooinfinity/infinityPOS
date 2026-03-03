<?php

declare(strict_types=1);

use App\Actions\StockMovement\CreateStockMovement;
use App\Enums\StockMovementTypeEnum;
use App\Models\Batch;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\StockTransfer;

it('records a stock in movement with default note', function (): void {
    $batch = Batch::factory()->withQuantity(50)->create();
    $purchase = Purchase::factory()->create();

    $action = resolve(CreateStockMovement::class);

    $movement = $action->recordIn($batch, 20, 50, $purchase);

    expect($movement)->toBeInstanceOf(StockMovement::class)
        ->and($movement->type)->toBe(StockMovementTypeEnum::In)
        ->and($movement->quantity)->toBe(20)
        ->and($movement->previous_quantity)->toBe(50)
        ->and($movement->current_quantity)->toBe(70)
        ->and($movement->warehouse_id)->toBe($batch->warehouse_id)
        ->and($movement->product_id)->toBe($batch->product_id)
        ->and($movement->batch_id)->toBe($batch->id)
        ->and($movement->note)->toBe('Stock received');
});

it('records a stock in movement with custom note', function (): void {
    $batch = Batch::factory()->withQuantity(50)->create();
    $purchase = Purchase::factory()->create();

    $action = resolve(CreateStockMovement::class);

    $movement = $action->recordIn($batch, 10, 50, $purchase, null, 'Purchase receipt');

    expect($movement->note)->toBe('Purchase receipt');
});

it('records a stock out movement with default note', function (): void {
    $batch = Batch::factory()->withQuantity(100)->create();
    $sale = Sale::factory()->create();

    $action = resolve(CreateStockMovement::class);

    $movement = $action->recordOut($batch, 30, 100, $sale);

    expect($movement)->toBeInstanceOf(StockMovement::class)
        ->and($movement->type)->toBe(StockMovementTypeEnum::Out)
        ->and($movement->quantity)->toBe(30)
        ->and($movement->previous_quantity)->toBe(100)
        ->and($movement->current_quantity)->toBe(70)
        ->and($movement->note)->toBe('Stock deducted');
});

it('records a stock out movement with custom note', function (): void {
    $batch = Batch::factory()->withQuantity(100)->create();
    $sale = Sale::factory()->create();

    $action = resolve(CreateStockMovement::class);

    $movement = $action->recordOut($batch, 10, 100, $sale, null, 'Purchase return completed - stock removed');

    expect($movement->note)->toBe('Purchase return completed - stock removed');
});

it('records a transfer out movement', function (): void {
    $batch = Batch::factory()->withQuantity(100)->create();
    $transfer = StockTransfer::factory()->create();

    $action = resolve(CreateStockMovement::class);

    $movement = $action->recordTransfer($batch, 25, 100, StockMovementTypeEnum::Out, $transfer);

    expect($movement->type)->toBe(StockMovementTypeEnum::Transfer)
        ->and($movement->previous_quantity)->toBe(100)
        ->and($movement->current_quantity)->toBe(75)
        ->and($movement->note)->toBe('Stock transfer out');
});

it('records a transfer in movement', function (): void {
    $batch = Batch::factory()->withQuantity(0)->create();
    $transfer = StockTransfer::factory()->create();

    $action = resolve(CreateStockMovement::class);

    $movement = $action->recordTransfer($batch, 25, 0, StockMovementTypeEnum::In, $transfer);

    expect($movement->type)->toBe(StockMovementTypeEnum::Transfer)
        ->and($movement->previous_quantity)->toBe(0)
        ->and($movement->current_quantity)->toBe(25)
        ->and($movement->note)->toBe('Stock transfer in');
});

it('stores the reference type and id', function (): void {
    $batch = Batch::factory()->withQuantity(50)->create();
    $sale = Sale::factory()->create();

    $action = resolve(CreateStockMovement::class);

    $movement = $action->recordOut($batch, 10, 50, $sale, $sale->user_id);

    expect($movement->reference_type)->toBe(Sale::class)
        ->and($movement->reference_id)->toBe($sale->id)
        ->and($movement->user_id)->toBe($sale->user_id);
});
