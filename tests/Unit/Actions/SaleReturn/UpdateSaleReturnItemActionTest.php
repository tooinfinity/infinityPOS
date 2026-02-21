<?php

declare(strict_types=1);

use App\Actions\SaleReturn\UpdateSaleReturnItemAction;
use App\Data\SaleReturn\UpdateSaleReturnItemData;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;

it('updates item quantity in pending sale return', function (): void {
    $saleReturn = SaleReturn::factory()->pending()->create();
    $item = SaleReturnItem::factory()->forSaleReturn($saleReturn)->create([
        'quantity' => 5,
        'unit_price' => 100,
        'subtotal' => 500,
    ]);

    $action = resolve(UpdateSaleReturnItemAction::class);

    $updated = $action->handle($item, new UpdateSaleReturnItemData(
        quantity: 10,
    ));

    expect($updated->quantity)->toBe(10)
        ->and($updated->subtotal)->toBe(1000);
});

it('updates item unit price in pending sale return', function (): void {
    $saleReturn = SaleReturn::factory()->pending()->create();
    $item = SaleReturnItem::factory()->forSaleReturn($saleReturn)->create([
        'quantity' => 5,
        'unit_price' => 100,
        'subtotal' => 500,
    ]);

    $action = resolve(UpdateSaleReturnItemAction::class);

    $updated = $action->handle($item, new UpdateSaleReturnItemData(
        unit_price: 200,
    ));

    expect($updated->unit_price)->toBe(200)
        ->and($updated->subtotal)->toBe(1000);
});

it('recalculates total amount when updating item', function (): void {
    $saleReturn = SaleReturn::factory()->pending()->create();
    $item = SaleReturnItem::factory()->forSaleReturn($saleReturn)->create([
        'quantity' => 5,
        'unit_price' => 100,
        'subtotal' => 500,
    ]);

    $action = resolve(UpdateSaleReturnItemAction::class);

    $action->handle($item, new UpdateSaleReturnItemData(
        quantity: 10,
    ));

    expect($saleReturn->fresh()->total_amount)->toBe(1000);
});

it('throws exception when updating item in non-pending return', function (): void {
    $saleReturn = SaleReturn::factory()->completed()->create();
    $item = SaleReturnItem::factory()->forSaleReturn($saleReturn)->create([
        'quantity' => 5,
        'unit_price' => 100,
        'subtotal' => 500,
    ]);

    $action = resolve(UpdateSaleReturnItemAction::class);

    $action->handle($item, new UpdateSaleReturnItemData(
        quantity: 10,
    ));
})->throws(RuntimeException::class, 'Cannot update items in a non-pending');
