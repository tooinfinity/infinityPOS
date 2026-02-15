<?php

declare(strict_types=1);

use App\Actions\StockMovement\RecordStockMovement;
use App\Enums\StockMovementTypeEnum;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;

it('may record a stock movement with required fields', function (): void {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();
    $sale = Sale::factory()->create();

    $action = resolve(RecordStockMovement::class);

    $movement = $action->handle([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'type' => StockMovementTypeEnum::Out,
        'quantity' => 10,
        'previous_quantity' => 100,
        'current_quantity' => 90,
        'reference_type' => 'Sale',
        'reference_id' => $sale->id,
    ]);

    expect($movement)->toBeInstanceOf(StockMovement::class)
        ->and($movement->warehouse_id)->toBe($warehouse->id)
        ->and($movement->product_id)->toBe($product->id)
        ->and($movement->type)->toBe(StockMovementTypeEnum::Out)
        ->and($movement->quantity)->toBe(10)
        ->and($movement->previous_quantity)->toBe(100)
        ->and($movement->current_quantity)->toBe(90)
        ->and($movement->reference_type)->toBe('Sale')
        ->and($movement->reference_id)->toBe($sale->id)
        ->and($movement->exists)->toBeTrue();
});

it('records stock movement with all optional fields', function (): void {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();
    $batch = Batch::factory()->create();
    $user = User::factory()->create();
    $sale = Sale::factory()->create();

    $action = resolve(RecordStockMovement::class);

    $movement = $action->handle([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'batch_id' => $batch->id,
        'user_id' => $user->id,
        'type' => StockMovementTypeEnum::In,
        'quantity' => 50,
        'previous_quantity' => 0,
        'current_quantity' => 50,
        'reference_type' => 'Sale',
        'reference_id' => $sale->id,
        'note' => 'Stock received from supplier',
        'created_at' => now()->subDay(),
    ]);

    expect($movement->batch_id)->toBe($batch->id)
        ->and($movement->user_id)->toBe($user->id)
        ->and($movement->note)->toBe('Stock received from supplier');
});

it('records stock in movement', function (): void {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();
    $sale = Sale::factory()->create();

    $action = resolve(RecordStockMovement::class);

    $movement = $action->handle([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'type' => StockMovementTypeEnum::In,
        'quantity' => 100,
        'previous_quantity' => 50,
        'current_quantity' => 150,
        'reference_type' => 'Sale',
        'reference_id' => $sale->id,
    ]);

    expect($movement->type)->toBe(StockMovementTypeEnum::In)
        ->and($movement->previous_quantity)->toBe(50)
        ->and($movement->current_quantity)->toBe(150);
});

it('records stock out movement', function (): void {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();
    $sale = Sale::factory()->create();

    $action = resolve(RecordStockMovement::class);

    $movement = $action->handle([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'type' => StockMovementTypeEnum::Out,
        'quantity' => 25,
        'previous_quantity' => 100,
        'current_quantity' => 75,
        'reference_type' => 'Sale',
        'reference_id' => $sale->id,
    ]);

    expect($movement->type)->toBe(StockMovementTypeEnum::Out);
});

it('records adjustment movement', function (): void {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();
    $sale = Sale::factory()->create();

    $action = resolve(RecordStockMovement::class);

    $movement = $action->handle([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'type' => StockMovementTypeEnum::Adjustment,
        'quantity' => 10,
        'previous_quantity' => 100,
        'current_quantity' => 110,
        'reference_type' => 'Sale',
        'reference_id' => $sale->id,
        'note' => 'Inventory adjustment - found extra stock',
    ]);

    expect($movement->type)->toBe(StockMovementTypeEnum::Adjustment)
        ->and($movement->note)->toBe('Inventory adjustment - found extra stock');
});

it('records transfer movement', function (): void {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();
    $sale = Sale::factory()->create();

    $action = resolve(RecordStockMovement::class);

    $movement = $action->handle([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'type' => StockMovementTypeEnum::Transfer,
        'quantity' => 30,
        'previous_quantity' => 200,
        'current_quantity' => 170,
        'reference_type' => 'Sale',
        'reference_id' => $sale->id,
    ]);

    expect($movement->type)->toBe(StockMovementTypeEnum::Transfer);
});

it('records movement without batch and user', function (): void {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();
    $sale = Sale::factory()->create();

    $action = resolve(RecordStockMovement::class);

    $movement = $action->handle([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'batch_id' => null,
        'user_id' => null,
        'type' => StockMovementTypeEnum::Out,
        'quantity' => 5,
        'previous_quantity' => 50,
        'current_quantity' => 45,
        'reference_type' => 'Sale',
        'reference_id' => $sale->id,
    ]);

    expect($movement->batch_id)->toBeNull()
        ->and($movement->user_id)->toBeNull();
});
