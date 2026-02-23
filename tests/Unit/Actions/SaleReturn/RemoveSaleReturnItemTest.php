<?php

declare(strict_types=1);

use App\Actions\SaleReturn\RemoveSaleReturnItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;

it('removes item from pending sale return', function (): void {
    $saleReturn = SaleReturn::factory()->pending()->create();
    $item = SaleReturnItem::factory()->forSaleReturn($saleReturn)->create();

    $action = resolve(RemoveSaleReturnItem::class);

    $result = $action->handle($item);

    expect($result)->toBeTrue()
        ->and($item->exists)->toBeFalse();
});

it('recalculates total amount when removing item', function (): void {
    $saleReturn = SaleReturn::factory()->pending()->create();
    $item1 = SaleReturnItem::factory()->forSaleReturn($saleReturn)->create([
        'quantity' => 5,
        'unit_price' => 100,
        'subtotal' => 500,
    ]);
    $item2 = SaleReturnItem::factory()->forSaleReturn($saleReturn)->create([
        'quantity' => 5,
        'unit_price' => 100,
        'subtotal' => 500,
    ]);

    $action = resolve(RemoveSaleReturnItem::class);

    $action->handle($item1);

    expect($saleReturn->fresh()->total_amount)->toBe(500);
});

it('throws exception when removing item from non-pending return', function (): void {
    $saleReturn = SaleReturn::factory()->completed()->create();
    $item = SaleReturnItem::factory()->forSaleReturn($saleReturn)->create();

    $action = resolve(RemoveSaleReturnItem::class);

    $action->handle($item);
})->throws(RuntimeException::class, 'Cannot remove items from a non-pending');
