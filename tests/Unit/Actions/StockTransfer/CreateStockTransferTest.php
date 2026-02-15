<?php

declare(strict_types=1);

use App\Actions\StockTransfer\CreateStockTransfer;
use App\Models\Batch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Warehouse;

it('may create a stock transfer with required fields', function (): void {
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    $action = resolve(CreateStockTransfer::class);

    $transfer = $action->handle([
        'from_warehouse_id' => $fromWarehouse->id,
        'to_warehouse_id' => $toWarehouse->id,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 10],
        ],
    ]);

    expect($transfer)->toBeInstanceOf(StockTransfer::class)
        ->and($transfer->from_warehouse_id)->toBe($fromWarehouse->id)
        ->and($transfer->to_warehouse_id)->toBe($toWarehouse->id)
        ->and($transfer->reference_no)->toStartWith('STF-')
        ->and($transfer->exists)->toBeTrue();
});

it('auto-generates reference number', function (): void {
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    $action = resolve(CreateStockTransfer::class);

    $transfer = $action->handle([
        'from_warehouse_id' => $fromWarehouse->id,
        'to_warehouse_id' => $toWarehouse->id,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 10],
        ],
    ]);

    expect($transfer->reference_no)
        ->toStartWith('STF-')
        ->and(mb_strlen($transfer->reference_no))->toBeGreaterThan(10);
});

it('creates transfer with all optional fields', function (): void {
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();
    $user = App\Models\User::factory()->create();

    $action = resolve(CreateStockTransfer::class);

    $transfer = $action->handle([
        'from_warehouse_id' => $fromWarehouse->id,
        'to_warehouse_id' => $toWarehouse->id,
        'note' => 'Transfer note',
        'transfer_date' => now()->addDay(),
        'user_id' => $user->id,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 10],
        ],
    ]);

    expect($transfer->note)->toBe('Transfer note')
        ->and($transfer->user_id)->toBe($user->id);
});

it('throws exception when source and destination are the same', function (): void {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    $action = resolve(CreateStockTransfer::class);

    expect(fn () => $action->handle([
        'from_warehouse_id' => $warehouse->id,
        'to_warehouse_id' => $warehouse->id,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 10],
        ],
    ]))->toThrow(RuntimeException::class, 'Source and destination warehouse cannot be the same.');
});

it('creates transfer with items without batches', function (): void {
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    $action = resolve(CreateStockTransfer::class);

    $transfer = $action->handle([
        'from_warehouse_id' => $fromWarehouse->id,
        'to_warehouse_id' => $toWarehouse->id,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 10],
        ],
    ]);

    $item = StockTransferItem::query()->where('stock_transfer_id', $transfer->id)->first();

    expect($item)->not->toBeNull()
        ->and($item->product_id)->toBe($product->id)
        ->and($item->batch_id)->toBeNull()
        ->and($item->quantity)->toBe(10);
});

it('creates transfer with items with batches', function (): void {
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();
    $batch = Batch::factory()->create();

    $action = resolve(CreateStockTransfer::class);

    $transfer = $action->handle([
        'from_warehouse_id' => $fromWarehouse->id,
        'to_warehouse_id' => $toWarehouse->id,
        'items' => [
            ['product_id' => $batch->product_id, 'batch_id' => $batch->id, 'quantity' => 25],
        ],
    ]);

    $item = StockTransferItem::query()->where('stock_transfer_id', $transfer->id)->first();

    expect($item->batch_id)->toBe($batch->id)
        ->and($item->quantity)->toBe(25);
});

it('creates transfer with multiple items', function (): void {
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();
    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();

    $action = resolve(CreateStockTransfer::class);

    $transfer = $action->handle([
        'from_warehouse_id' => $fromWarehouse->id,
        'to_warehouse_id' => $toWarehouse->id,
        'items' => [
            ['product_id' => $product1->id, 'quantity' => 10],
            ['product_id' => $product2->id, 'quantity' => 20],
        ],
    ]);

    expect(StockTransferItem::query()->where('stock_transfer_id', $transfer->id)->count())->toBe(2);
});
