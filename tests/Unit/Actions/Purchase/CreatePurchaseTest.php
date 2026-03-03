<?php

declare(strict_types=1);

use App\Actions\Purchase\CreatePurchase;
use App\Data\Purchase\CreatePurchaseData;
use App\Data\Purchase\PurchaseItemData;
use App\Enums\PaymentStatusEnum;
use App\Enums\PurchaseStatusEnum;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelData\DataCollection;

beforeEach(function (): void {
    Storage::fake('public');
    DB::statement('PRAGMA foreign_keys = ON');
});

it('may create a purchase with required fields', function (): void {
    $supplier = Supplier::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    $action = resolve(CreatePurchase::class);

    $items = new DataCollection(PurchaseItemData::class, [
        new PurchaseItemData(product_id: $product->id, quantity: 10, unit_cost: 500),
    ]);

    $data = new CreatePurchaseData(
        supplier_id: $supplier->id,
        warehouse_id: $warehouse->id,
        purchase_date: now(),
        note: null,
        user_id: null,
        document: null,
        items: $items,
    );

    $purchase = $action->handle($data);

    expect($purchase)->toBeInstanceOf(Purchase::class)
        ->and($purchase->supplier_id)->toBe($supplier->id)
        ->and($purchase->warehouse_id)->toBe($warehouse->id)
        ->and($purchase->reference_no)->toStartWith('PUR-')
        ->and($purchase->status)->toBe(PurchaseStatusEnum::Pending)
        ->and($purchase->payment_status)->toBe(PaymentStatusEnum::Unpaid)
        ->and($purchase->total_amount)->toBe(5000)
        ->and($purchase->exists)->toBeTrue();
});

it('auto-generates reference number', function (): void {
    $supplier = Supplier::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    $action = resolve(CreatePurchase::class);

    $items = new DataCollection(PurchaseItemData::class, [
        new PurchaseItemData(product_id: $product->id, quantity: 5, unit_cost: 100),
    ]);

    $data = new CreatePurchaseData(
        supplier_id: $supplier->id,
        warehouse_id: $warehouse->id,
        purchase_date: now(),
        note: null,
        user_id: null,
        document: null,
        items: $items,
    );

    $purchase = $action->handle($data);

    expect($purchase->reference_no)
        ->toStartWith('PUR-')
        ->and(mb_strlen($purchase->reference_no))->toBeGreaterThan(10);
});

it('creates purchase with multiple items', function (): void {
    $supplier = Supplier::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();

    $action = resolve(CreatePurchase::class);

    $items = new DataCollection(PurchaseItemData::class, [
        new PurchaseItemData(product_id: $product1->id, quantity: 10, unit_cost: 100),
        new PurchaseItemData(product_id: $product2->id, quantity: 5, unit_cost: 200),
    ]);

    $data = new CreatePurchaseData(
        supplier_id: $supplier->id,
        warehouse_id: $warehouse->id,
        purchase_date: now(),
        note: null,
        user_id: null,
        document: null,
        items: $items,
    );

    $purchase = $action->handle($data);

    expect(PurchaseItem::query()->where('purchase_id', $purchase->id)->count())->toBe(2)
        ->and($purchase->total_amount)->toBe(2000);
});

it('creates purchase with document', function (): void {
    $supplier = Supplier::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    $action = resolve(CreatePurchase::class);

    $document = UploadedFile::fake()->image('invoice.jpg');

    $items = new DataCollection(PurchaseItemData::class, [
        new PurchaseItemData(product_id: $product->id, quantity: 10, unit_cost: 100),
    ]);

    $data = new CreatePurchaseData(
        supplier_id: $supplier->id,
        warehouse_id: $warehouse->id,
        purchase_date: now(),
        note: 'Test note',
        user_id: null,
        document: $document,
        items: $items,
    );

    $purchase = $action->handle($data);

    expect($purchase->document)->not->toBeNull()
        ->and($purchase->note)->toBe('Test note');

    Storage::disk('public')->assertExists($purchase->document);
});

it('calculates correct subtotal for each item', function (): void {
    $supplier = Supplier::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    $action = resolve(CreatePurchase::class);

    $items = new DataCollection(PurchaseItemData::class, [
        new PurchaseItemData(product_id: $product->id, quantity: 15, unit_cost: 250),
    ]);

    $data = new CreatePurchaseData(
        supplier_id: $supplier->id,
        warehouse_id: $warehouse->id,
        purchase_date: now(),
        note: null,
        user_id: null,
        document: null,
        items: $items,
    );

    $purchase = $action->handle($data);

    $item = PurchaseItem::query()->where('purchase_id', $purchase->id)->first();

    expect($item->subtotal)->toBe(3750)
        ->and($item->received_quantity)->toBe(0);
});

it('stores purchase in database', function (): void {
    $supplier = Supplier::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    $action = resolve(CreatePurchase::class);

    $items = new DataCollection(PurchaseItemData::class, [
        new PurchaseItemData(product_id: $product->id, quantity: 10, unit_cost: 100),
    ]);

    $data = new CreatePurchaseData(
        supplier_id: $supplier->id,
        warehouse_id: $warehouse->id,
        purchase_date: now(),
        note: null,
        user_id: null,
        document: null,
        items: $items,
    );

    $purchase = $action->handle($data);

    $this->assertDatabaseHas('purchases', [
        'id' => $purchase->id,
        'supplier_id' => $supplier->id,
        'warehouse_id' => $warehouse->id,
        'status' => PurchaseStatusEnum::Pending->value,
        'total_amount' => 1000,
    ]);
});

it('deletes uploaded document when transaction fails', function (): void {
    $supplier = Supplier::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $action = resolve(CreatePurchase::class);

    $document = UploadedFile::fake()->image('invoice.jpg');

    $filesBefore = Storage::disk('public')->files('purchases/documents');

    $items = new DataCollection(PurchaseItemData::class, [
        new PurchaseItemData(product_id: 99999, quantity: 10, unit_cost: 100),
    ]);

    $data = new CreatePurchaseData(
        supplier_id: $supplier->id,
        warehouse_id: $warehouse->id,
        purchase_date: now(),
        note: null,
        user_id: null,
        document: $document,
        items: $items,
    );

    try {
        $action->handle($data);
    } catch (Throwable) {
    }

    $filesAfter = Storage::disk('public')->files('purchases/documents');

    expect($filesBefore)->toBe($filesAfter);
});
