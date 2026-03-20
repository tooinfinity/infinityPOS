<?php

declare(strict_types=1);

use App\Actions\Purchase\ReceivePurchase;
use App\Data\Purchase\ReceivePurchaseData;
use App\Data\Purchase\ReceivePurchaseItemData;
use App\Enums\PurchaseStatusEnum;
use App\Models\Batch;
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

it('creates batch if not exists when receiving', function (): void {
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

    $action->handle($purchase, $data);

    expect($product->batches()->where('warehouse_id', $warehouse->id)->exists())->toBeTrue();
});

it('adds stock to existing batch when receiving', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();
    $batch = Batch::factory()->forProduct($product)->forWarehouse($warehouse)->create([
        'quantity' => 50,
        'cost_amount' => 10000,
        'expires_at' => null, // Must match purchase item expiry
    ]);

    $purchase = Purchase::factory()->for($warehouse)->for($supplier)->create([
        'status' => PurchaseStatusEnum::Pending,
    ]);

    $purchaseItem = $purchase->items()->create([
        'product_id' => $product->id,
        'quantity' => 10,
        'received_quantity' => 0,
        'unit_cost' => 10000,
        'subtotal' => 100000,
        'expires_at' => null,
    ]);

    $action = resolve(ReceivePurchase::class);

    $data = new ReceivePurchaseData(
        items: new DataCollection(ReceivePurchaseItemData::class, [
            new ReceivePurchaseItemData(
                purchase_item_id: $purchaseItem->id,
                received_quantity: 5,
                expires_at: null,
            ),
        ]),
    );

    $action->handle($purchase, $data);

    $updatedBatch = Batch::query()->find($batch->id);

    expect($updatedBatch->quantity)->toBe(55);
});

it('sets purchase status to Received when all items fully received', function (): void {
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

it('records stock movement when receiving', function (): void {
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
                received_quantity: 8,
                expires_at: null,
            ),
        ]),
    );

    $action->handle($purchase, $data);

    $batch = $product->batches()->where('warehouse_id', $warehouse->id)->first();

    expect($batch->stockMovements()->count())->toBe(1)
        ->and($batch->stockMovements()->first()->type)->toBe(App\Enums\StockMovementTypeEnum::In);
});

it('updates purchase item received quantity', function (): void {
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
                received_quantity: 5,
                expires_at: null,
            ),
        ]),
    );

    $action->handle($purchase, $data);

    expect($purchaseItem->fresh()->received_quantity)->toBe(5);
});

it('receives partial quantity and keeps status as Pending', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();

    $purchase = Purchase::factory()->for($warehouse)->for($supplier)->create([
        'status' => PurchaseStatusEnum::Pending,
    ]);

    $purchaseItem = $purchase->items()->create([
        'product_id' => $product->id,
        'quantity' => 20,
        'received_quantity' => 0,
        'unit_cost' => 10000,
        'subtotal' => 200000,
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

    expect($receivedPurchase->status)->toBe(PurchaseStatusEnum::Pending);
});
