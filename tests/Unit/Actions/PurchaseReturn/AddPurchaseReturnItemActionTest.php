<?php

declare(strict_types=1);

use App\Actions\PurchaseReturn\AddPurchaseReturnItemAction;
use App\Data\PurchaseReturn\PurchaseReturnItemData;
use App\Models\Batch;
use App\Models\PurchaseReturn;

it('adds item to pending purchase return', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->pending()->create();
    $batch = Batch::factory()->withQuantity(100)->create();

    $action = resolve(AddPurchaseReturnItemAction::class);

    $item = $action->handle($purchaseReturn, new PurchaseReturnItemData(
        product_id: $batch->product_id,
        batch_id: $batch->id,
        quantity: 5,
        unit_cost: 200,
    ));

    expect($item)
        ->toBeInstanceOf(App\Models\PurchaseReturnItem::class)
        ->and($item->purchase_return_id)->toBe($purchaseReturn->id)
        ->and($item->quantity)->toBe(5)
        ->and($item->subtotal)->toBe(1000);
});

it('recalculates total amount when adding item', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->pending()->create(['total_amount' => 0]);
    $batch = Batch::factory()->withQuantity(100)->create();

    $action = resolve(AddPurchaseReturnItemAction::class);

    $action->handle($purchaseReturn, new PurchaseReturnItemData(
        product_id: $batch->product_id,
        batch_id: $batch->id,
        quantity: 10,
        unit_cost: 500,
    ));

    expect($purchaseReturn->fresh()->total_amount)->toBe(5000);
});

it('throws exception when adding item to non-pending return', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->completed()->create();
    $batch = Batch::factory()->withQuantity(100)->create();

    $action = resolve(AddPurchaseReturnItemAction::class);

    $action->handle($purchaseReturn, new PurchaseReturnItemData(
        product_id: $batch->product_id,
        batch_id: $batch->id,
        quantity: 5,
        unit_cost: 200,
    ));
})->throws(RuntimeException::class, 'Cannot add items to a non-pending');
