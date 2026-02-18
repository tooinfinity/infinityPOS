<?php

declare(strict_types=1);

use App\Actions\Purchase\MarkPurchaseAsOrderedAction;
use App\Enums\PurchaseStatusEnum;
use App\Exceptions\StateTransitionException;
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

    $action = resolve(MarkPurchaseAsOrderedAction::class);

    $orderedPurchase = $action->handle($purchase);

    expect($orderedPurchase->status)->toBe(PurchaseStatusEnum::Ordered);
});

it('throws StateTransitionException when marking non-pending purchase as ordered', function (): void {
    $purchase = Purchase::factory()->received()->create();

    $action = resolve(MarkPurchaseAsOrderedAction::class);

    expect(fn () => $action->handle($purchase))
        ->toThrow(StateTransitionException::class);
});

it('throws exception when marking empty purchase as ordered', function (): void {
    $purchase = Purchase::factory()->pending()->create();

    $action = resolve(MarkPurchaseAsOrderedAction::class);

    expect(fn () => $action->handle($purchase))
        ->toThrow(RuntimeException::class, 'Cannot order a purchase with no items.');
});

it('persists status change to database', function (): void {
    $purchase = Purchase::factory()->pending()->create();
    $product = Product::factory()->create();

    PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
    ]);

    $action = resolve(MarkPurchaseAsOrderedAction::class);

    $action->handle($purchase);

    $this->assertDatabaseHas('purchases', [
        'id' => $purchase->id,
        'status' => PurchaseStatusEnum::Ordered->value,
    ]);
});
