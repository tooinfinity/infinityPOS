<?php

declare(strict_types=1);

use App\Actions\SaleReturn\CreateSaleReturn;
use App\Data\SaleReturn\CreateSaleReturnData;
use App\Data\SaleReturn\SaleReturnItemData;
use App\Enums\PaymentStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Models\Batch;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Warehouse;
use Spatie\LaravelData\DataCollection;

it('creates a pending sale return with items', function (): void {
    $sale = Sale::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $batch = Batch::factory()->withQuantity(100)->create();

    $action = resolve(CreateSaleReturn::class);

    $items = new DataCollection(SaleReturnItemData::class, [
        new SaleReturnItemData(
            product_id: $batch->product_id,
            batch_id: $batch->id,
            quantity: 10,
            unit_price: 500,
        ),
    ]);

    $data = new CreateSaleReturnData(
        sale_id: $sale->id,
        warehouse_id: $warehouse->id,
        user_id: null,
        return_date: now(),
        note: 'Test return',
        items: $items,
    );

    $saleReturn = $action->handle($data);

    expect($saleReturn)
        ->toBeInstanceOf(SaleReturn::class)
        ->and($saleReturn->sale_id)->toBe($sale->id)
        ->and($saleReturn->warehouse_id)->toBe($warehouse->id)
        ->and($saleReturn->reference_no)->toStartWith('SRET-')
        ->and($saleReturn->status)->toBe(ReturnStatusEnum::Pending)
        ->and($saleReturn->payment_status)->toBe(PaymentStatusEnum::Unpaid)
        ->and($saleReturn->total_amount)->toBe(5000)
        ->and($saleReturn->paid_amount)->toBe(0)
        ->and($saleReturn->note)->toBe('Test return')
        ->and($saleReturn->exists)->toBeTrue();
});

it('auto-generates unique reference number', function (): void {
    $sale = Sale::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $batch = Batch::factory()->withQuantity(100)->create();

    $action = resolve(CreateSaleReturn::class);

    $items = new DataCollection(SaleReturnItemData::class, [
        new SaleReturnItemData(
            product_id: $batch->product_id,
            batch_id: $batch->id,
            quantity: 5,
            unit_price: 100,
        ),
    ]);

    $data = new CreateSaleReturnData(
        sale_id: $sale->id,
        warehouse_id: $warehouse->id,
        user_id: null,
        return_date: now(),
        note: null,
        items: $items,
    );

    $saleReturn = $action->handle($data);

    expect($saleReturn->reference_no)
        ->toStartWith('SRET-')
        ->and(mb_strlen($saleReturn->reference_no))->toBeGreaterThan(10);
});

it('creates sale return with multiple items', function (): void {
    $sale = Sale::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $batch1 = Batch::factory()->withQuantity(100)->create();
    $batch2 = Batch::factory()->withQuantity(100)->create();

    $action = resolve(CreateSaleReturn::class);

    $items = new DataCollection(SaleReturnItemData::class, [
        new SaleReturnItemData(
            product_id: $batch1->product_id,
            batch_id: $batch1->id,
            quantity: 10,
            unit_price: 100,
        ),
        new SaleReturnItemData(
            product_id: $batch2->product_id,
            batch_id: $batch2->id,
            quantity: 5,
            unit_price: 200,
        ),
    ]);

    $data = new CreateSaleReturnData(
        sale_id: $sale->id,
        warehouse_id: $warehouse->id,
        user_id: null,
        return_date: now(),
        note: null,
        items: $items,
    );

    $saleReturn = $action->handle($data);

    expect(SaleReturnItem::query()->where('sale_return_id', $saleReturn->id)->count())->toBe(2)
        ->and($saleReturn->total_amount)->toBe(2000);
});

it('calculates correct subtotal for each item', function (): void {
    $sale = Sale::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $batch = Batch::factory()->withQuantity(100)->create();

    $action = resolve(CreateSaleReturn::class);

    $items = new DataCollection(SaleReturnItemData::class, [
        new SaleReturnItemData(
            product_id: $batch->product_id,
            batch_id: $batch->id,
            quantity: 15,
            unit_price: 250,
        ),
    ]);

    $data = new CreateSaleReturnData(
        sale_id: $sale->id,
        warehouse_id: $warehouse->id,
        user_id: null,
        return_date: now(),
        note: null,
        items: $items,
    );

    $saleReturn = $action->handle($data);

    $item = SaleReturnItem::query()->where('sale_return_id', $saleReturn->id)->first();

    expect($item->subtotal)->toBe(3750);
});
