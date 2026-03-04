<?php

declare(strict_types=1);

use App\Actions\Purchase\AddPurchaseItem;
use App\Actions\Purchase\RemovePurchaseItem;
use App\Actions\Purchase\UpdatePurchaseItem;
use App\Actions\Shared\RecalculateParentTotal;
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

    $action = resolve(AddPurchaseItem::class);

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

    $action = resolve(AddPurchaseItem::class);

    $data = new PurchaseItemData(
        product_id: $product->id,
        quantity: 10,
        unit_cost: 100,
    );

    $action->handle($purchase, $data);

    expect($purchase->fresh()->total_amount)->toBe(1500);
});

it('may update item quantity', function (): void {
    $purchase = Purchase::factory()->pending()->create();
    $item = PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
        'quantity' => 10,
        'unit_cost' => 100,
        'subtotal' => 1000,
    ]);

    $action = resolve(UpdatePurchaseItem::class);

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

    $action = resolve(UpdatePurchaseItem::class);

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

    $action = resolve(UpdatePurchaseItem::class);

    $data = new UpdatePurchaseItemData(
        quantity: 20,
        unit_cost: 150,
    );

    $action->handle($item, $data);

    expect($item->fresh()->subtotal)->toBe(3000)
        ->and($purchase->fresh()->total_amount)->toBe(3000);
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

    $action = resolve(RemovePurchaseItem::class);

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

    $action = new RemovePurchaseItem(
        new RecalculateParentTotal(),
    );

    $result = $action->handle($item, deleteIfEmpty: true);

    expect($result)->toBeNull();
    $this->assertDatabaseMissing('purchases', ['id' => $purchase->id]);
});

it('keeps purchase when removing last item with deleteIfEmpty false', function (): void {
    $purchase = Purchase::factory()->pending()->create();

    $item = PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
    ]);

    $action = new RemovePurchaseItem(
        new RecalculateParentTotal(),
    );

    $result = $action->handle($item, deleteIfEmpty: false);

    expect($result)->toBeInstanceOf(Purchase::class)
        ->and($result->total_amount)->toBe(0);
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

    $action = resolve(RemovePurchaseItem::class);
    $action->handle($item);

    expect($purchase->fresh()->total_amount)->toBe(1000);
});
