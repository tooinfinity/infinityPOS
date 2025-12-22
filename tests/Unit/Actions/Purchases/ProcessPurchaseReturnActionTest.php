<?php

declare(strict_types=1);

use App\Actions\Purchases\ProcessPurchaseReturn;
use App\Data\Purchases\ProcessPurchaseReturnData;
use App\Data\Purchases\ProcessPurchaseReturnItemData;
use App\Enums\PurchaseReturnStatusEnum;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;

it('may process a purchase return', function (): void {
    $user = User::factory()->create();
    $supplier = Supplier::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);
    $product = Product::factory()->create(['created_by' => $user->id]);
    $purchase = Purchase::factory()->create([
        'supplier_id' => $supplier->id,
        'store_id' => $store->id,
        'created_by' => $user->id,
    ]);
    $purchaseItem = PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'quantity' => 20,
        'cost' => 1000,
    ]);

    $action = resolve(ProcessPurchaseReturn::class);

    $itemData = ProcessPurchaseReturnItemData::from([
        'product_id' => $product->id,
        'purchase_item_id' => $purchaseItem->id,
        'quantity' => 5,
        'cost' => 1000,
        'total' => 5000,
        'batch_number' => 'BATCH-001',
    ]);

    $data = ProcessPurchaseReturnData::from([
        'reference' => 'PR-001',
        'purchase_id' => $purchase->id,
        'supplier_id' => $supplier->id,
        'store_id' => $store->id,
        'subtotal' => 5000,
        'discount' => 0,
        'tax' => 0,
        'total' => 5000,
        'reason' => 'Defective items',
        'notes' => 'Return test',
        'items' => [$itemData],
        'created_by' => $user->id,
    ]);

    $purchaseReturn = $action->handle($data);

    expect($purchaseReturn)->toBeInstanceOf(PurchaseReturn::class)
        ->and($purchaseReturn->reference)->toBe('PR-001')
        ->and($purchaseReturn->purchase_id)->toBe($purchase->id)
        ->and($purchaseReturn->supplier_id)->toBe($supplier->id)
        ->and($purchaseReturn->store_id)->toBe($store->id)
        ->and($purchaseReturn->status)->toBe(PurchaseReturnStatusEnum::PENDING)
        ->and($purchaseReturn->reason)->toBe('Defective items')
        ->and($purchaseReturn->notes)->toBe('Return test')
        ->and($purchaseReturn->created_by)->toBe($user->id)
        ->and($purchaseReturn->items)->toHaveCount(1)
        ->and($purchaseReturn->items->first()->product_id)->toBe($product->id)
        ->and($purchaseReturn->items->first()->quantity)->toBe(5)
        ->and($purchaseReturn->items->first()->cost)->toBe(1000);
});
