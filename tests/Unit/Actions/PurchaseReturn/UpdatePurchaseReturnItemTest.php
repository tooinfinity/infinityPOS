<?php

declare(strict_types=1);

use App\Actions\PurchaseReturn\UpdatePurchaseReturnItem;
use App\Data\PurchaseReturn\UpdatePurchaseReturnItemData;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Warehouse;

it('updates item quantity in pending purchase return', function (): void {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();
    $batch = Batch::factory()->forWarehouse($warehouse)->forProduct($product)->withQuantity(100)->create();
    $purchase = Purchase::factory()->forWarehouse($warehouse)->create();
    PurchaseItem::factory()->forPurchase($purchase)->forProduct($product)->forBatch($batch)->create([
        'quantity' => 20,
        'unit_cost' => 100,
    ]);

    $purchaseReturn = PurchaseReturn::factory()->forPurchase($purchase)->forWarehouse($warehouse)->pending()->create();
    $item = PurchaseReturnItem::factory()->forPurchaseReturn($purchaseReturn)->forProduct($product)->forBatch($batch)->create([
        'quantity' => 5,
        'unit_cost' => 100,
        'subtotal' => 500,
    ]);

    $action = resolve(UpdatePurchaseReturnItem::class);

    $updated = $action->handle($item, new UpdatePurchaseReturnItemData(quantity: 10));

    expect($updated->quantity)->toBe(10)
        ->and($updated->subtotal)->toBe(1000);
});

it('updates item unit_cost in pending purchase return', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->pending()->create();
    $item = PurchaseReturnItem::factory()->forPurchaseReturn($purchaseReturn)->create([
        'quantity' => 5,
        'unit_cost' => 100,
        'subtotal' => 500,
    ]);

    $action = resolve(UpdatePurchaseReturnItem::class);

    $updated = $action->handle($item, new UpdatePurchaseReturnItemData(unit_cost: 200));

    expect($updated->unit_cost)->toBe(200)
        ->and($updated->subtotal)->toBe(1000);
});
