<?php

declare(strict_types=1);

use App\Actions\Purchases\CreatePurchase;
use App\Data\Purchases\CreatePurchaseData;
use App\Data\Purchases\CreatePurchaseItemData;
use App\Enums\PurchaseStatusEnum;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;

it('may create a purchase', function (): void {
    $user = User::factory()->create();
    $supplier = Supplier::factory()->create(['created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);
    $product = Product::factory()->create(['created_by' => $user->id]);
    $action = resolve(CreatePurchase::class);

    $itemData = CreatePurchaseItemData::from([
        'product_id' => $product->id,
        'quantity' => 10,
        'cost' => 5000,
        'discount' => 500,
        'tax_amount' => 500,
        'total' => 50000,
        'batch_number' => 'BATCH-001',
        'expiry_date' => now()->addYear()->toDateString(),
    ]);

    $data = CreatePurchaseData::from([
        'reference' => 'PO-001',
        'supplier_id' => $supplier->id,
        'store_id' => $store->id,
        'subtotal' => 49500,
        'discount' => 500,
        'tax' => 500,
        'total' => 50000,
        'notes' => 'Test purchase',
        'items' => [$itemData],
        'created_by' => $user->id,
    ]);

    $purchase = $action->handle($data);

    expect($purchase)->toBeInstanceOf(Purchase::class)
        ->and($purchase->reference)->toBe('PO-001')
        ->and($purchase->supplier_id)->toBe($supplier->id)
        ->and($purchase->store_id)->toBe($store->id)
        ->and($purchase->status)->toBe(PurchaseStatusEnum::PENDING)
        ->and($purchase->notes)->toBe('Test purchase')
        ->and($purchase->created_by)->toBe($user->id)
        ->and($purchase->items)->toHaveCount(1)
        ->and($purchase->items->first()->product_id)->toBe($product->id)
        ->and($purchase->items->first()->quantity)->toBe(10)
        ->and($purchase->items->first()->cost)->toBe(5000);
});
