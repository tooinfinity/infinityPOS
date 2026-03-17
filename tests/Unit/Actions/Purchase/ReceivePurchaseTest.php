<?php

declare(strict_types=1);

use App\Actions\Purchase\ReceivePurchase;
use App\Data\Purchase\ReceivePurchaseData;
use App\Data\Purchase\ReceivePurchaseItemData;
use App\Enums\PurchaseStatusEnum;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use Spatie\LaravelData\DataCollection;

it('may receive a purchase', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();

    $purchase = Purchase::factory()->for($warehouse)->for($supplier)->create([
        'status' => PurchaseStatusEnum::Pending,
    ]);

    $purchaseItem = $purchase->items()->create([
        'product_id' => $product->id,
        'quantity' => 10,
        'received_quantity' => 0,
        'unit_cost' => 10000,
        'subtotal' => 100000,
    ]);

    $action = resolve(ReceivePurchase::class);

    $data = new ReceivePurchaseData(
        items: new DataCollection(ReceivePurchaseItemData::class, [
            new ReceivePurchaseItemData(
                purchase_item_id: $purchaseItem->id,
                received_quantity: 10,
                expires_at: null,
            ),
        ]),
    );

    $receivedPurchase = $action->handle($purchase, $data);

    expect($receivedPurchase->status)->toBe(PurchaseStatusEnum::Received);
});

it('throws exception when purchase cannot transition to received', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();

    $purchase = Purchase::factory()->for($warehouse)->for($supplier)->create([
        'status' => PurchaseStatusEnum::Received,
    ]);

    $purchaseItem = $purchase->items()->create([
        'product_id' => $product->id,
        'quantity' => 10,
        'received_quantity' => 0,
        'unit_cost' => 10000,
        'subtotal' => 100000,
    ]);

    $action = resolve(ReceivePurchase::class);

    $data = new ReceivePurchaseData(
        items: new DataCollection(ReceivePurchaseItemData::class, [
            new ReceivePurchaseItemData(
                purchase_item_id: $purchaseItem->id,
                received_quantity: 10,
                expires_at: null,
            ),
        ]),
    );

    expect(fn () => $action->handle($purchase, $data))->toThrow(App\Exceptions\StateTransitionException::class);
});

it('throws exception when item does not belong to purchase', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();

    $purchase = Purchase::factory()->for($warehouse)->for($supplier)->create([
        'status' => PurchaseStatusEnum::Pending,
    ]);

    $purchaseItem = $purchase->items()->create([
        'product_id' => $product->id,
        'quantity' => 10,
        'received_quantity' => 0,
        'unit_cost' => 10000,
        'subtotal' => 100000,
    ]);

    $anotherPurchase = Purchase::factory()->for($warehouse)->for($supplier)->create([
        'status' => PurchaseStatusEnum::Pending,
    ]);

    $anotherItem = $anotherPurchase->items()->create([
        'product_id' => $product->id,
        'quantity' => 5,
        'received_quantity' => 0,
        'unit_cost' => 10000,
        'subtotal' => 50000,
    ]);

    $action = resolve(ReceivePurchase::class);

    $data = new ReceivePurchaseData(
        items: new DataCollection(ReceivePurchaseItemData::class, [
            new ReceivePurchaseItemData(
                purchase_item_id: $anotherItem->id,
                received_quantity: 5,
                expires_at: null,
            ),
        ]),
    );

    expect(fn () => $action->handle($purchase, $data))->toThrow(App\Exceptions\ItemNotFoundException::class);
});

it('skips items with zero received quantity', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();

    $purchase = Purchase::factory()->for($warehouse)->for($supplier)->create([
        'status' => PurchaseStatusEnum::Pending,
    ]);

    $purchaseItem = $purchase->items()->create([
        'product_id' => $product->id,
        'quantity' => 10,
        'received_quantity' => 0,
        'unit_cost' => 10000,
        'subtotal' => 100000,
    ]);

    $action = resolve(ReceivePurchase::class);

    $data = new ReceivePurchaseData(
        items: new DataCollection(ReceivePurchaseItemData::class, [
            new ReceivePurchaseItemData(
                purchase_item_id: $purchaseItem->id,
                received_quantity: 0,
                expires_at: null,
            ),
        ]),
    );

    $receivedPurchase = $action->handle($purchase, $data);

    expect($receivedPurchase->status)->toBe(PurchaseStatusEnum::Pending);
});
