<?php

declare(strict_types=1);

use App\Actions\SaleReturn\AddSaleReturnItem;
use App\Data\SaleReturn\SaleReturnItemData;
use App\Models\Batch;
use App\Models\SaleReturn;

it('adds item to pending sale return', function (): void {
    $saleReturn = SaleReturn::factory()->pending()->create();
    $batch = Batch::factory()->withQuantity(100)->create();

    $action = resolve(AddSaleReturnItem::class);

    $item = $action->handle($saleReturn, new SaleReturnItemData(
        product_id: $batch->product_id,
        batch_id: $batch->id,
        quantity: 5,
        unit_price: 200,
    ));

    expect($item)
        ->toBeInstanceOf(App\Models\SaleReturnItem::class)
        ->and($item->sale_return_id)->toBe($saleReturn->id)
        ->and($item->product_id)->toBe($batch->product_id)
        ->and($item->quantity)->toBe(5)
        ->and($item->subtotal)->toBe(1000);
});

it('recalculates total amount when adding item', function (): void {
    $saleReturn = SaleReturn::factory()->pending()->create([
        'total_amount' => 0,
    ]);
    $batch = Batch::factory()->withQuantity(100)->create();

    $action = resolve(AddSaleReturnItem::class);

    $action->handle($saleReturn, new SaleReturnItemData(
        product_id: $batch->product_id,
        batch_id: $batch->id,
        quantity: 10,
        unit_price: 500,
    ));

    expect($saleReturn->fresh()->total_amount)->toBe(5000);
});

it('throws exception when adding item to non-pending return', function (): void {
    $saleReturn = SaleReturn::factory()->completed()->create();
    $batch = Batch::factory()->withQuantity(100)->create();

    $action = resolve(AddSaleReturnItem::class);

    $action->handle($saleReturn, new SaleReturnItemData(
        product_id: $batch->product_id,
        batch_id: $batch->id,
        quantity: 5,
        unit_price: 200,
    ));
})->throws(RuntimeException::class, 'Cannot add items to a non-pending');
