<?php

declare(strict_types=1);

use App\Actions\Purchases\CalculatePurchaseTotals;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\User;

it('may calculate purchase totals', function (): void {
    $user = User::factory()->create();
    $product1 = Product::factory()->create(['created_by' => $user->id]);
    $product2 = Product::factory()->create(['created_by' => $user->id]);

    $purchase = Purchase::factory()->create([
        'subtotal' => 0,
        'tax' => 0,
        'total' => 0,
        'created_by' => $user->id,
    ]);

    // Item 1: 10 units @ 1000 = 10000, discount 500, tax 500, total 10000
    PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $product1->id,
        'quantity' => 10,
        'cost' => 1000,
        'discount' => 500,
        'tax_amount' => 500,
        'total' => 10000,
    ]);

    // Item 2: 5 units @ 2000 = 10000, discount 0, tax 1000, total 11000
    PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $product2->id,
        'quantity' => 5,
        'cost' => 2000,
        'discount' => 0,
        'tax_amount' => 1000,
        'total' => 11000,
    ]);

    $action = resolve(CalculatePurchaseTotals::class);

    $updatedPurchase = $action->handle($purchase);

    // Subtotal: (10*1000 - 500) + (5*2000 - 0) = 9500 + 10000 = 19500
    expect($updatedPurchase->subtotal)->toBe(19500)
        ->and($updatedPurchase->tax)->toBe(1500)
        ->and($updatedPurchase->total)->toBe(21000);
});
