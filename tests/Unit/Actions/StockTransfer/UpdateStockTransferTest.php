<?php

declare(strict_types=1);

use App\Actions\StockTransfer\UpdateStockTransfer;
use App\Data\StockTransfer\StockTransferData;
use App\Data\StockTransfer\StockTransferItemData;
use App\Models\Batch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Unit;
use App\Models\Warehouse;

it('may update a stock transfer with required fields', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();
    $transfer = StockTransfer::factory()
        ->for($fromWarehouse, 'fromWarehouse')
        ->for($toWarehouse, 'toWarehouse')
        ->pending()
        ->create();

    $action = resolve(UpdateStockTransfer::class);

    $data = new StockTransferData(
        from_warehouse_id: $fromWarehouse->id,
        to_warehouse_id: $toWarehouse->id,
        transfer_date: now(),
        note: null,
        items: new Spatie\LaravelData\DataCollection(StockTransferItemData::class, [
            new StockTransferItemData(
                product_id: $product->id,
                batch_id: null,
                quantity: 20,
            ),
        ]),
    );

    $updated = $action->handle($transfer, $data);

    expect($updated->items)->toHaveCount(1)
        ->and($updated->items->first()->quantity)->toBe(20);
});

it('may update a stock transfer with multiple items', function (): void {
    $unit = Unit::factory()->create();
    $product1 = Product::factory()->for($unit)->create();
    $product2 = Product::factory()->for($unit)->create();
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();
    $transfer = StockTransfer::factory()
        ->for($fromWarehouse, 'fromWarehouse')
        ->for($toWarehouse, 'toWarehouse')
        ->pending()
        ->create();

    $action = resolve(UpdateStockTransfer::class);

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

    $updated = $action->handle($transfer, $data);

    expect($updated->items)->toHaveCount(2);
});

it('may update a stock transfer with note', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();
    $transfer = StockTransfer::factory()
        ->for($fromWarehouse, 'fromWarehouse')
        ->for($toWarehouse, 'toWarehouse')
        ->pending()
        ->create();

    $action = resolve(UpdateStockTransfer::class);

    $data = new StockTransferData(
        from_warehouse_id: $fromWarehouse->id,
        to_warehouse_id: $toWarehouse->id,
        transfer_date: now(),
        note: 'Updated transfer note',
        items: new Spatie\LaravelData\DataCollection(StockTransferItemData::class, [
            new StockTransferItemData(
                product_id: $product->id,
                batch_id: null,
                quantity: 10,
            ),
        ]),
    );

    $updated = $action->handle($transfer, $data);

    expect($updated->note)->toBe('Updated transfer note');
});

it('throws exception when updating non-pending transfer', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();
    $transfer = StockTransfer::factory()
        ->for($fromWarehouse, 'fromWarehouse')
        ->for($toWarehouse, 'toWarehouse')
        ->completed()
        ->create();

    $action = resolve(UpdateStockTransfer::class);

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

    expect(fn () => $action->handle($transfer, $data))->toThrow(App\Exceptions\InvalidOperationException::class);
});

it('deletes old items and creates new ones on update', function (): void {
    $unit = Unit::factory()->create();
    $product1 = Product::factory()->for($unit)->create();
    $product2 = Product::factory()->for($unit)->create();
    $batch = Batch::factory()->for($product1)->create(['quantity' => 100]);
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();
    $transfer = StockTransfer::factory()
        ->for($fromWarehouse, 'fromWarehouse')
        ->for($toWarehouse, 'toWarehouse')
        ->pending()
        ->create();

    $transfer->items()->create([
        'product_id' => $product1->id,
        'batch_id' => $batch->id,
        'quantity' => 5,
    ]);

    $action = resolve(UpdateStockTransfer::class);

    $data = new StockTransferData(
        from_warehouse_id: $fromWarehouse->id,
        to_warehouse_id: $toWarehouse->id,
        transfer_date: now(),
        note: null,
        items: new Spatie\LaravelData\DataCollection(StockTransferItemData::class, [
            new StockTransferItemData(
                product_id: $product2->id,
                batch_id: null,
                quantity: 2,
            ),
        ]),
    );

    $updated = $action->handle($transfer, $data);

    expect($updated->items)->toHaveCount(1)
        ->and($updated->items->first()->product_id)->toBe($product2->id);
});
