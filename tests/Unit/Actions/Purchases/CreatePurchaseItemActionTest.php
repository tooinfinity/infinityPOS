<?php

declare(strict_types=1);

use App\Actions\Purchases\CreatePurchaseItem;
use App\Data\Purchases\CreatePurchaseItemData;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\User;

it('may create a purchase item', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['created_by' => $user->id]);
    $purchase = Purchase::factory()->create(['created_by' => $user->id]);
    $action = resolve(CreatePurchaseItem::class);

    $data = CreatePurchaseItemData::from([
        'product_id' => $product->id,
        'quantity' => 15,
        'cost' => 3000,
        'discount' => 300,
        'tax_amount' => 450,
        'total' => 45450,
        'batch_number' => 'BATCH-123',
        'expiry_date' => now()->addMonths(6)->toDateString(),
    ]);

    $purchaseItem = $action->handle($purchase, $data);

    expect($purchaseItem)->toBeInstanceOf(PurchaseItem::class)
        ->and($purchaseItem->purchase_id)->toBe($purchase->id)
        ->and($purchaseItem->product_id)->toBe($product->id)
        ->and($purchaseItem->quantity)->toBe(15)
        ->and($purchaseItem->cost)->toBe(3000)
        ->and($purchaseItem->discount)->toBe(300)
        ->and($purchaseItem->tax_amount)->toBe(450)
        ->and($purchaseItem->total)->toBe(45450)
        ->and($purchaseItem->batch_number)->toBe('BATCH-123');
});
