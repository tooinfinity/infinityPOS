<?php

declare(strict_types=1);

use App\Actions\StockTransfer\CreateStockTransfer;
use App\Data\StockTransfer\StockTransferData;
use App\Data\StockTransfer\StockTransferItemData;
use App\Enums\StockTransferStatusEnum;
use App\Models\Batch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Unit;
use App\Models\Warehouse;

it('may create a stock transfer with required fields', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();

    $action = resolve(CreateStockTransfer::class);

    $data = new StockTransferData(
        from_warehouse_id: $fromWarehouse->id,
        to_warehouse_id: $toWarehouse->id,
        transfer_date: now(),
        note: null,
        items: new Spatie\LaravelData\DataCollection(StockTransferItemData::class, [
            new StockTransferItemData(
                product_id: $product->id,
                batch_id: null,
                quantity: 10,
            ),
        ]),
    );

    $transfer = $action->handle($data);

    expect($transfer)->toBeInstanceOf(StockTransfer::class)
        ->and($transfer->from_warehouse_id)->toBe($fromWarehouse->id)
        ->and($transfer->to_warehouse_id)->toBe($toWarehouse->id)
        ->and($transfer->status)->toBe(StockTransferStatusEnum::Pending)
        ->and($transfer->exists)->toBeTrue()
        ->and($transfer->items)->toHaveCount(1);
});

it('may create a stock transfer with multiple items', function (): void {
    $unit = Unit::factory()->create();
    $product1 = Product::factory()->for($unit)->create();
    $product2 = Product::factory()->for($unit)->create();
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();

    $action = resolve(CreateStockTransfer::class);

    $data = new StockTransferData(
        from_warehouse_id: $fromWarehouse->id,
        to_warehouse_id: $toWarehouse->id,
        transfer_date: now(),
        note: null,
        items: new Spatie\LaravelData\DataCollection(StockTransferItemData::class, [
            new StockTransferItemData(
                product_id: $product1->id,
                batch_id: null,
                quantity: 5,
            ),
            new StockTransferItemData(
                product_id: $product2->id,
                batch_id: null,
                quantity: 5,
            ),
        ]),
    );

    $transfer = $action->handle($data);

    expect($transfer->items)->toHaveCount(2);
});

it('may create a stock transfer with note', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();

    $action = resolve(CreateStockTransfer::class);

    $data = new StockTransferData(
        from_warehouse_id: $fromWarehouse->id,
        to_warehouse_id: $toWarehouse->id,
        transfer_date: now(),
        note: 'Transfer to main warehouse',
        items: new Spatie\LaravelData\DataCollection(StockTransferItemData::class, [
            new StockTransferItemData(
                product_id: $product->id,
                batch_id: null,
                quantity: 10,
            ),
        ]),
    );

    $transfer = $action->handle($data);

    expect($transfer->note)->toBe('Transfer to main warehouse');
});

it('may create a stock transfer with batch', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $batch = Batch::factory()->for($product)->create(['quantity' => 100]);
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();

    $action = resolve(CreateStockTransfer::class);

    $data = new StockTransferData(
        from_warehouse_id: $fromWarehouse->id,
        to_warehouse_id: $toWarehouse->id,
        transfer_date: now(),
        note: null,
        items: new Spatie\LaravelData\DataCollection(StockTransferItemData::class, [
            new StockTransferItemData(
                product_id: $product->id,
                batch_id: $batch->id,
                quantity: 10,
            ),
        ]),
    );

    $transfer = $action->handle($data);

    expect($transfer->items->first()->batch_id)->toBe($batch->id);
});

it('generates reference number', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();

    $action = resolve(CreateStockTransfer::class);

    $data = new StockTransferData(
        from_warehouse_id: $fromWarehouse->id,
        to_warehouse_id: $toWarehouse->id,
        transfer_date: now(),
        note: null,
        items: new Spatie\LaravelData\DataCollection(StockTransferItemData::class, [
            new StockTransferItemData(
                product_id: $product->id,
                batch_id: null,
                quantity: 10,
            ),
        ]),
    );

    $transfer = $action->handle($data);

    expect($transfer->reference_no)->toStartWith('TRF-')
        ->and($transfer->reference_no)->toHaveLength(17);
});

it('may create a stock transfer with custom transfer date', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();
    $customDate = now()->addDays(5);

    $action = resolve(CreateStockTransfer::class);

    $data = new StockTransferData(
        from_warehouse_id: $fromWarehouse->id,
        to_warehouse_id: $toWarehouse->id,
        transfer_date: $customDate,
        note: null,
        items: new Spatie\LaravelData\DataCollection(StockTransferItemData::class, [
            new StockTransferItemData(
                product_id: $product->id,
                batch_id: null,
                quantity: 10,
            ),
        ]),
    );

    $transfer = $action->handle($data);

    expect($transfer->transfer_date->toDateString())->toBe($customDate->toDateString());
});

it('may create StockTransferData from model', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();
    $transfer = StockTransfer::factory()
        ->for($fromWarehouse, 'fromWarehouse')
        ->for($toWarehouse, 'toWarehouse')
        ->pending()
        ->create();
    $transfer->items()->create([
        'product_id' => $product->id,
        'batch_id' => null,
        'quantity' => 5,
    ]);

    $data = StockTransferData::fromModel($transfer);

    expect($data->from_warehouse_id)->toBe($fromWarehouse->id)
        ->and($data->to_warehouse_id)->toBe($toWarehouse->id)
        ->and($data->items)->toHaveCount(1);
});
