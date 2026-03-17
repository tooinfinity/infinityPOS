<?php

declare(strict_types=1);

use App\Actions\Purchase\UpdatePurchase;
use App\Data\Purchase\PurchaseData;
use App\Data\Purchase\PurchaseItemData;
use App\Enums\PurchaseStatusEnum;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;

it('may update a purchase with required fields', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();
    $purchase = Purchase::factory()->for($warehouse)->for($supplier)->pending()->create();

    $action = resolve(UpdatePurchase::class);

    $data = new PurchaseData(
        supplier_id: $supplier->id,
        warehouse_id: $warehouse->id,
        status: PurchaseStatusEnum::Pending,
        purchase_date: now(),
        total_amount: 150000,
        note: null,
        items: new Spatie\LaravelData\DataCollection(PurchaseItemData::class, [
            new PurchaseItemData(
                product_id: $product->id,
                quantity: 15,
                unit_cost: 10000,
                expires_at: null,
            ),
        ]),
    );

    $updated = $action->handle($purchase, $data);

    expect($updated->total_amount)->toBe(150000)
        ->and($updated->items)->toHaveCount(1)
        ->and($updated->items->first()->quantity)->toBe(15);
});

it('may update a purchase with multiple items', function (): void {
    $unit = Unit::factory()->create();
    $product1 = Product::factory()->for($unit)->create();
    $product2 = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();
    $purchase = Purchase::factory()->for($warehouse)->for($supplier)->pending()->create();

    $action = resolve(UpdatePurchase::class);

    $data = new PurchaseData(
        supplier_id: $supplier->id,
        warehouse_id: $warehouse->id,
        status: PurchaseStatusEnum::Pending,
        purchase_date: now(),
        total_amount: 100000,
        note: null,
        items: new Spatie\LaravelData\DataCollection(PurchaseItemData::class, [
            new PurchaseItemData(
                product_id: $product1->id,
                quantity: 5,
                unit_cost: 5000,
                expires_at: null,
            ),
            new PurchaseItemData(
                product_id: $product2->id,
                quantity: 5,
                unit_cost: 5000,
                expires_at: null,
            ),
        ]),
    );

    $updated = $action->handle($purchase, $data);

    expect($updated->items)->toHaveCount(2);
});

it('may update a purchase with note', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();
    $purchase = Purchase::factory()->for($warehouse)->for($supplier)->pending()->create();

    $action = resolve(UpdatePurchase::class);

    $data = new PurchaseData(
        supplier_id: $supplier->id,
        warehouse_id: $warehouse->id,
        status: PurchaseStatusEnum::Pending,
        purchase_date: now(),
        total_amount: 100000,
        note: 'Updated purchase note',
        items: new Spatie\LaravelData\DataCollection(PurchaseItemData::class, [
            new PurchaseItemData(
                product_id: $product->id,
                quantity: 10,
                unit_cost: 10000,
                expires_at: null,
            ),
        ]),
    );

    $updated = $action->handle($purchase, $data);

    expect($updated->note)->toBe('Updated purchase note');
});

it('throws exception when updating non-pending purchase', function (): void {
    $unit = Unit::factory()->create();
    $product = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();
    $purchase = Purchase::factory()->for($warehouse)->for($supplier)->ordered()->create();

    $action = resolve(UpdatePurchase::class);

    $data = new PurchaseData(
        supplier_id: $supplier->id,
        warehouse_id: $warehouse->id,
        status: PurchaseStatusEnum::Ordered,
        purchase_date: now(),
        total_amount: 100000,
        note: null,
        items: new Spatie\LaravelData\DataCollection(PurchaseItemData::class, [
            new PurchaseItemData(
                product_id: $product->id,
                quantity: 10,
                unit_cost: 10000,
                expires_at: null,
            ),
        ]),
    );

    expect(fn () => $action->handle($purchase, $data))->toThrow(App\Exceptions\InvalidOperationException::class);
});

it('deletes old items and creates new ones on update', function (): void {
    $unit = Unit::factory()->create();
    $product1 = Product::factory()->for($unit)->create();
    $product2 = Product::factory()->for($unit)->create();
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();
    $purchase = Purchase::factory()->for($warehouse)->for($supplier)->pending()->create();

    $purchase->items()->create([
        'product_id' => $product1->id,
        'quantity' => 5,
        'received_quantity' => 0,
        'unit_cost' => 5000,
        'subtotal' => 25000,
    ]);

    $action = resolve(UpdatePurchase::class);

    $data = new PurchaseData(
        supplier_id: $supplier->id,
        warehouse_id: $warehouse->id,
        status: PurchaseStatusEnum::Pending,
        purchase_date: now(),
        total_amount: 10000,
        note: null,
        items: new Spatie\LaravelData\DataCollection(PurchaseItemData::class, [
            new PurchaseItemData(
                product_id: $product2->id,
                quantity: 2,
                unit_cost: 5000,
                expires_at: null,
            ),
        ]),
    );

    $updated = $action->handle($purchase, $data);

    expect($updated->items)->toHaveCount(1)
        ->and($updated->items->first()->product_id)->toBe($product2->id);
});
