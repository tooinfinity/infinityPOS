<?php

declare(strict_types=1);

use App\Actions\PurchaseReturn\UpdatePurchaseReturnItemAction;
use App\Data\PurchaseReturn\UpdatePurchaseReturnItemData;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;

it('updates item quantity in pending purchase return', function (): void {
    $item = PurchaseReturnItem::factory()->create([
        'quantity' => 5,
        'unit_cost' => 100,
        'subtotal' => 500,
    ]);

    $action = resolve(UpdatePurchaseReturnItemAction::class);

    $updated = $action->handle($item, new UpdatePurchaseReturnItemData(quantity: 10));

    expect($updated->quantity)->toBe(10)
        ->and($updated->subtotal)->toBe(1000);
});

it('throws exception when updating item in non-pending return', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->completed()->create();
    $item = PurchaseReturnItem::factory()->forPurchaseReturn($purchaseReturn)->create();

    $action = resolve(UpdatePurchaseReturnItemAction::class);

    $action->handle($item, new UpdatePurchaseReturnItemData(quantity: 10));
})->throws(RuntimeException::class, 'Cannot update items in a non-pending');
