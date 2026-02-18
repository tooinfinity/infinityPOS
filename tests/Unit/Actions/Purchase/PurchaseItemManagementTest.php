<?php

declare(strict_types=1);

use App\Actions\Purchase\AddPurchaseItemAction;
use App\Actions\Purchase\RemovePurchaseItemAction;
use App\Actions\Purchase\UpdatePurchaseItemAction;
use App\Data\Purchase\PurchaseItemData;
use App\Data\Purchase\UpdatePurchaseItemData;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Spatie\LaravelData\Optional;

it('may add item to pending purchase', function (): void {
    $purchase = Purchase::factory()->pending()->create([
        'total_amount' => 0,
    ]);
    $product = Product::factory()->create();

    $action = resolve(AddPurchaseItemAction::class);

    $data = new PurchaseItemData(
        product_id: $product->id,
        quantity: 10,
        unit_cost: 100,
    );

    $item = $action->handle($purchase, $data);

    expect($item)->toBeInstanceOf(PurchaseItem::class)
        ->and($item->purchase_id)->toBe($purchase->id)
        ->and($item->product_id)->toBe($product->id)
        ->and($item->quantity)->toBe(10)
        ->and($item->unit_cost)->toBe(100)
        ->and($item->subtotal)->toBe(1000);
});

it('recalculates total when adding item', function (): void {
    $purchase = Purchase::factory()->pending()->create([
        'total_amount' => 500,
    ]);
    $product = Product::factory()->create();

    PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'subtotal' => 500,
    ]);

    $action = resolve(AddPurchaseItemAction::class);

    $data = new PurchaseItemData(
        product_id: $product->id,
        quantity: 10,
        unit_cost: 100,
    );

    $action->handle($purchase, $data);

    expect($purchase->fresh()->total_amount)->toBe(1500);
});

it('throws exception when adding item to non-pending purchase', function (): void {
    $purchase = Purchase::factory()->received()->create();
    $product = Product::factory()->create();

    $action = resolve(AddPurchaseItemAction::class);

    $data = new PurchaseItemData(
        product_id: $product->id,
        quantity: 10,
        unit_cost: 100,
    );

    expect(fn () => $action->handle($purchase, $data))
        ->toThrow(RuntimeException::class, 'Items can only be added to pending purchases.');
});

it('may update item quantity', function (): void {
    $purchase = Purchase::factory()->pending()->create();
    $item = PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'quantity' => 10,
        'unit_cost' => 100,
        'subtotal' => 1000,
    ]);

    $action = resolve(UpdatePurchaseItemAction::class);

    $data = new UpdatePurchaseItemData(
        quantity: 20,
        unit_cost: Optional::create(),
    );

    $updatedItem = $action->handle($item, $data);

    expect($updatedItem->quantity)->toBe(20)
        ->and($updatedItem->unit_cost)->toBe(100)
        ->and($updatedItem->subtotal)->toBe(2000);
});

it('may update item unit cost', function (): void {
    $purchase = Purchase::factory()->pending()->create();
    $item = PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'quantity' => 10,
        'unit_cost' => 100,
        'subtotal' => 1000,
    ]);

    $action = resolve(UpdatePurchaseItemAction::class);

    $data = new UpdatePurchaseItemData(
        quantity: Optional::create(),
        unit_cost: 150,
    );

    $updatedItem = $action->handle($item, $data);

    expect($updatedItem->quantity)->toBe(10)
        ->and($updatedItem->unit_cost)->toBe(150)
        ->and($updatedItem->subtotal)->toBe(1500);
});

it('recalculates subtotal and purchase total on update', function (): void {
    $purchase = Purchase::factory()->pending()->create([
        'total_amount' => 1000,
    ]);

    $item = PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'quantity' => 10,
        'unit_cost' => 100,
        'subtotal' => 1000,
    ]);

    $action = resolve(UpdatePurchaseItemAction::class);

    $data = new UpdatePurchaseItemData(
        quantity: 20,
        unit_cost: 150,
    );

    $action->handle($item, $data);

    expect($item->fresh()->subtotal)->toBe(3000)
        ->and($purchase->fresh()->total_amount)->toBe(3000);
});

it('throws exception when updating item on non-pending purchase', function (): void {
    $purchase = Purchase::factory()->ordered()->create();
    $item = PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
    ]);

    $action = resolve(UpdatePurchaseItemAction::class);

    $data = new UpdatePurchaseItemData(
        quantity: 20,
        unit_cost: Optional::create(),
    );

    expect(fn () => $action->handle($item, $data))
        ->toThrow(RuntimeException::class, 'Items can only be updated on pending purchases.');
});

it('may remove item from pending purchase', function (): void {
    $purchase = Purchase::factory()->pending()->create([
        'total_amount' => 1500,
    ]);

    $item1 = PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'subtotal' => 1000,
    ]);

    PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'subtotal' => 500,
    ]);

    $action = resolve(RemovePurchaseItemAction::class);

    $result = $action->handle($item1);

    expect($result)->toBeInstanceOf(Purchase::class)
        ->and($result->id)->toBe($purchase->id)
        ->and($result->fresh()->total_amount)->toBe(500);
});

it('deletes purchase when removing last item', function (): void {
    $purchase = Purchase::factory()->pending()->create();

    $item = PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
    ]);

    $action = new RemovePurchaseItemAction(deleteIfEmpty: true);

    $result = $action->handle($item);

    expect($result)->toBeNull();
    $this->assertDatabaseMissing('purchases', ['id' => $purchase->id]);
});

it('keeps purchase when removing last item with deleteIfEmpty false', function (): void {
    $purchase = Purchase::factory()->pending()->create();

    $item = PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
    ]);

    $action = new RemovePurchaseItemAction(deleteIfEmpty: false);

    $result = $action->handle($item);

    expect($result)->toBeInstanceOf(Purchase::class)
        ->and($result->total_amount)->toBe(0);
});

it('throws exception when removing item from non-pending purchase', function (): void {
    $purchase = Purchase::factory()->cancelled()->create();
    $item = PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
    ]);

    $action = resolve(RemovePurchaseItemAction::class);

    expect(fn () => $action->handle($item))
        ->toThrow(RuntimeException::class, 'Items can only be removed from pending purchases.');
});

it('recalculates total when removing item', function (): void {
    $purchase = Purchase::factory()->pending()->create([
        'total_amount' => 2000,
    ]);

    $item = PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'subtotal' => 1000,
    ]);

    PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'subtotal' => 1000,
    ]);

    $action = resolve(RemovePurchaseItemAction::class);
    $action->handle($item);

    expect($purchase->fresh()->total_amount)->toBe(1000);
});
