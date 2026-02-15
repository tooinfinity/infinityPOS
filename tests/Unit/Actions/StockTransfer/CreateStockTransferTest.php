<?php

declare(strict_types=1);

use App\Actions\StockTransfer\CreateStockTransfer;
use App\Data\StockTransfer\CreateStockTransferData;
use App\Data\StockTransfer\StockTransferItemData;
use App\Models\Batch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Warehouse;
use Spatie\LaravelData\DataCollection;

it('may create a stock transfer with required fields', function (): void {
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    $action = resolve(CreateStockTransfer::class);

    $items = new DataCollection(StockTransferItemData::class, [
        new StockTransferItemData(product_id: $product->id, batch_id: null, quantity: 10),
    ]);

    $data = new CreateStockTransferData(
        from_warehouse_id: $fromWarehouse->id,
        to_warehouse_id: $toWarehouse->id,
        note: null,
        transfer_date: now(),
        user_id: null,
        items: $items,
    );

    $transfer = $action->handle($data);

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

    $items = new DataCollection(StockTransferItemData::class, [
        new StockTransferItemData(product_id: $product->id, batch_id: null, quantity: 10),
    ]);

    $data = new CreateStockTransferData(
        from_warehouse_id: $fromWarehouse->id,
        to_warehouse_id: $toWarehouse->id,
        note: null,
        transfer_date: now(),
        user_id: null,
        items: $items,
    );

    $transfer = $action->handle($data);

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

    $items = new DataCollection(StockTransferItemData::class, [
        new StockTransferItemData(product_id: $product->id, batch_id: null, quantity: 10),
    ]);

    $data = new CreateStockTransferData(
        from_warehouse_id: $fromWarehouse->id,
        to_warehouse_id: $toWarehouse->id,
        note: 'Transfer note',
        transfer_date: now()->addDay(),
        user_id: $user->id,
        items: $items,
    );

    $transfer = $action->handle($data);

    expect($transfer->note)->toBe('Transfer note')
        ->and($transfer->user_id)->toBe($user->id);
});

it('throws exception when source and destination are the same', function (): void {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    $action = resolve(CreateStockTransfer::class);

    $items = new DataCollection(StockTransferItemData::class, [
        new StockTransferItemData(product_id: $product->id, batch_id: null, quantity: 10),
    ]);

    $data = new CreateStockTransferData(
        from_warehouse_id: $warehouse->id,
        to_warehouse_id: $warehouse->id,
        note: null,
        transfer_date: now(),
        user_id: null,
        items: $items,
    );

    expect(fn () => $action->handle($data))->toThrow(RuntimeException::class, 'Source and destination warehouse cannot be the same.');
});

it('creates transfer with items without batches', function (): void {
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    $action = resolve(CreateStockTransfer::class);

    $items = new DataCollection(StockTransferItemData::class, [
        new StockTransferItemData(product_id: $product->id, batch_id: null, quantity: 10),
    ]);

    $data = new CreateStockTransferData(
        from_warehouse_id: $fromWarehouse->id,
        to_warehouse_id: $toWarehouse->id,
        note: null,
        transfer_date: now(),
        user_id: null,
        items: $items,
    );

    $transfer = $action->handle($data);

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

    $items = new DataCollection(StockTransferItemData::class, [
        new StockTransferItemData(product_id: $batch->product_id, batch_id: $batch->id, quantity: 25),
    ]);

    $data = new CreateStockTransferData(
        from_warehouse_id: $fromWarehouse->id,
        to_warehouse_id: $toWarehouse->id,
        note: null,
        transfer_date: now(),
        user_id: null,
        items: $items,
    );

    $transfer = $action->handle($data);

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

    $items = new DataCollection(StockTransferItemData::class, [
        new StockTransferItemData(product_id: $product1->id, batch_id: null, quantity: 10),
        new StockTransferItemData(product_id: $product2->id, batch_id: null, quantity: 20),
    ]);

    $data = new CreateStockTransferData(
        from_warehouse_id: $fromWarehouse->id,
        to_warehouse_id: $toWarehouse->id,
        note: null,
        transfer_date: now(),
        user_id: null,
        items: $items,
    );

    $transfer = $action->handle($data);

    expect(StockTransferItem::query()->where('stock_transfer_id', $transfer->id)->count())->toBe(2);
});
