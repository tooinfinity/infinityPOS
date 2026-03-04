<?php

declare(strict_types=1);

use App\Actions\Purchase\MarkPurchaseAsOrdered;
use App\Enums\PurchaseStatusEnum;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;

it('may mark pending purchase as ordered', function (): void {
    $purchase = Purchase::factory()->pending()->create();
    $product = Product::factory()->create();

    PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
    ]);

    $action = resolve(MarkPurchaseAsOrdered::class);

    $orderedPurchase = $action->handle($purchase);

    expect($orderedPurchase->status)->toBe(PurchaseStatusEnum::Ordered);
});

it('persists status change to database', function (): void {
    $purchase = Purchase::factory()->pending()->create();
    $product = Product::factory()->create();

    PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
    ]);

    $action = resolve(MarkPurchaseAsOrdered::class);

    $action->handle($purchase);

    $this->assertDatabaseHas('purchases', [
        'id' => $purchase->id,
        'status' => PurchaseStatusEnum::Ordered->value,
    ]);
});
