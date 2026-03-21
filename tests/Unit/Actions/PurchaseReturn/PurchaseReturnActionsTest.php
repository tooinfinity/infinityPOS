<?php

declare(strict_types=1);

use App\Actions\PurchaseReturn\CompletePurchaseReturn;
use App\Actions\PurchaseReturn\CreatePurchaseReturn;
use App\Actions\PurchaseReturn\ResolveReturnableQuantity;
use App\Actions\SaleReturn\CompleteSaleReturn;
use App\Actions\SaleReturn\CreateSaleReturn;
use App\Actions\SaleReturn\ResolveReturnableQuantity as SaleReturnResolveReturnableQuantity;
use App\Data\PurchaseReturn\PurchaseReturnData;
use App\Data\PurchaseReturn\PurchaseReturnItemData;
use App\Data\SaleReturn\SaleReturnData;
use App\Data\SaleReturn\SaleReturnItemData;
use App\Enums\PaymentStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Exceptions\StateTransitionException;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use Spatie\LaravelData\DataCollection;

describe(ResolveReturnableQuantity::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->supplier = Supplier::factory()->create();
        $this->warehouse = Warehouse::factory()->create();
    });

    it('resolves returnable quantity for purchase', function (): void {
        $purchase = Purchase::factory()
            ->for($this->supplier)
            ->for($this->warehouse)
            ->received()
            ->create();

        PurchaseItem::factory()->forPurchase($purchase)->forProduct($this->product)->create([
            'received_quantity' => 100,
        ]);

        $action = resolve(ResolveReturnableQuantity::class);

        $result = $action->handle($purchase);

        expect($result->get($this->product->id))->toBe(100);
    });

    it('subtracts already returned quantity', function (): void {
        $purchase = Purchase::factory()
            ->for($this->supplier)
            ->for($this->warehouse)
            ->received()
            ->create();

        $batch = Batch::factory()->forProduct($this->product)->create();

        PurchaseItem::factory()->forPurchase($purchase)->forProduct($this->product)->create([
            'received_quantity' => 100,
        ]);

        PurchaseReturn::factory()
            ->forPurchase($purchase)
            ->forWarehouse($this->warehouse)
            ->create(['status' => ReturnStatusEnum::Completed]);

        PurchaseReturnItem::factory()
            ->forPurchaseReturn(PurchaseReturn::query()->latest()->first())
            ->forProduct($this->product)
            ->forBatch($batch)
            ->create(['quantity' => 30]);

        $action = resolve(ResolveReturnableQuantity::class);

        $result = $action->handle($purchase);

        expect($result->get($this->product->id))->toBe(70);
    });

    it('returns zero when all quantity has been returned', function (): void {
        $purchase = Purchase::factory()
            ->for($this->supplier)
            ->for($this->warehouse)
            ->received()
            ->create();

        $batch = Batch::factory()->forProduct($this->product)->create();

        PurchaseItem::factory()->forPurchase($purchase)->forProduct($this->product)->create([
            'received_quantity' => 50,
        ]);

        PurchaseReturn::factory()
            ->forPurchase($purchase)
            ->forWarehouse($this->warehouse)
            ->create(['status' => ReturnStatusEnum::Completed]);

        PurchaseReturnItem::factory()
            ->forPurchaseReturn(PurchaseReturn::query()->latest()->first())
            ->forProduct($this->product)
            ->forBatch($batch)
            ->create(['quantity' => 50]);

        $action = resolve(ResolveReturnableQuantity::class);

        $result = $action->handle($purchase);

        expect($result->get($this->product->id))->toBe(0);
    });

    it('validates returnable quantity', function (): void {
        $returnableMap = collect([$this->product->id => 50]);

        $items = new DataCollection(
            PurchaseReturnItemData::class,
            [
                new PurchaseReturnItemData(
                    product_id: $this->product->id,
                    batch_id: null,
                    quantity: 30,
                    unit_cost: 1000,
                ),
            ],
        );

        $action = resolve(ResolveReturnableQuantity::class);

        // Should not throw any exception
        $action->validate($returnableMap, $items);

        expect($returnableMap->get($this->product->id))->toBe(50);
    });

    it('throws exception when exceeding returnable quantity', function (): void {
        $returnableMap = collect([$this->product->id => 50]);

        $items = new DataCollection(
            PurchaseReturnItemData::class,
            [
                new PurchaseReturnItemData(
                    product_id: $this->product->id,
                    batch_id: null,
                    quantity: 100,
                    unit_cost: 1000,
                ),
            ],
        );

        $action = resolve(ResolveReturnableQuantity::class);

        expect(fn () => $action->validate($returnableMap, $items))
            ->toThrow(InvalidOperationException::class, 'exceeds returnable quantity');
    });
});

describe(CreatePurchaseReturn::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->supplier = Supplier::factory()->create();
        $this->warehouse = Warehouse::factory()->create();
        $this->batch = Batch::factory()->forProduct($this->product)->create();
    });

    it('may create a purchase return', function (): void {
        $purchase = Purchase::factory()
            ->for($this->supplier)
            ->for($this->warehouse)
            ->received()
            ->create();

        PurchaseItem::factory()->forPurchase($purchase)->forProduct($this->product)->create([
            'received_quantity' => 100,
        ]);

        $items = new DataCollection(
            PurchaseReturnItemData::class,
            [
                new PurchaseReturnItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 10,
                    unit_cost: 5000,
                ),
            ],
        );

        $data = new PurchaseReturnData(
            purchase_id: $purchase->id,
            warehouse_id: $this->warehouse->id,
            return_date: now()->toDateString(),
            note: 'Defective items',
            items: $items,
        );

        $action = resolve(CreatePurchaseReturn::class);

        $return = $action->handle($data);

        expect($return)->toBeInstanceOf(PurchaseReturn::class)
            ->and($return->purchase_id)->toBe($purchase->id)
            ->and($return->warehouse_id)->toBe($this->warehouse->id)
            ->and($return->status)->toBe(ReturnStatusEnum::Pending)
            ->and($return->total_amount)->toBe(50000)
            ->and($return->payment_status)->toBe(PaymentStatusEnum::Unpaid);
    });

    it('generates unique reference number', function (): void {
        $purchase = Purchase::factory()
            ->for($this->supplier)
            ->for($this->warehouse)
            ->received()
            ->create();

        PurchaseItem::factory()->forPurchase($purchase)->forProduct($this->product)->create([
            'received_quantity' => 100,
        ]);

        $items = new DataCollection(
            PurchaseReturnItemData::class,
            [
                new PurchaseReturnItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 5,
                    unit_cost: 1000,
                ),
            ],
        );

        $data = new PurchaseReturnData(
            purchase_id: $purchase->id,
            warehouse_id: $this->warehouse->id,
            return_date: now()->toDateString(),
            note: null,
            items: $items,
        );

        $action = resolve(CreatePurchaseReturn::class);

        $return1 = $action->handle($data);
        $return2 = $action->handle($data);

        expect($return1->reference_no)->not->toBe($return2->reference_no);
    });

    it('throws exception when purchase is not received', function (): void {
        $purchase = Purchase::factory()
            ->for($this->supplier)
            ->for($this->warehouse)
            ->pending()
            ->create();

        $items = new DataCollection(
            PurchaseReturnItemData::class,
            [
                new PurchaseReturnItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 5,
                    unit_cost: 1000,
                ),
            ],
        );

        $data = new PurchaseReturnData(
            purchase_id: $purchase->id,
            warehouse_id: $this->warehouse->id,
            return_date: now()->toDateString(),
            note: null,
            items: $items,
        );

        $action = resolve(CreatePurchaseReturn::class);

        expect(fn () => $action->handle($data))
            ->toThrow(InvalidOperationException::class, 'Returns can only be created for received purchases');
    });

    it('creates return items', function (): void {
        $purchase = Purchase::factory()
            ->for($this->supplier)
            ->for($this->warehouse)
            ->received()
            ->create();

        PurchaseItem::factory()->forPurchase($purchase)->forProduct($this->product)->create([
            'received_quantity' => 100,
        ]);

        $items = new DataCollection(
            PurchaseReturnItemData::class,
            [
                new PurchaseReturnItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 15,
                    unit_cost: 2000,
                ),
            ],
        );

        $data = new PurchaseReturnData(
            purchase_id: $purchase->id,
            warehouse_id: $this->warehouse->id,
            return_date: now()->toDateString(),
            note: null,
            items: $items,
        );

        $action = resolve(CreatePurchaseReturn::class);

        $return = $action->handle($data);

        expect($return->items)->toHaveCount(1)
            ->and($return->items->first()->product_id)->toBe($this->product->id)
            ->and($return->items->first()->batch_id)->toBe($this->batch->id)
            ->and($return->items->first()->quantity)->toBe(15)
            ->and($return->items->first()->unit_cost)->toBe(2000)
            ->and($return->items->first()->subtotal)->toBe(30000);
    });

    it('validates returnable quantity before creating return', function (): void {
        $purchase = Purchase::factory()
            ->for($this->supplier)
            ->for($this->warehouse)
            ->received()
            ->create();

        PurchaseItem::factory()->forPurchase($purchase)->forProduct($this->product)->create([
            'received_quantity' => 50,
        ]);

        $items = new DataCollection(
            PurchaseReturnItemData::class,
            [
                new PurchaseReturnItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 100, // Exceeds received quantity
                    unit_cost: 1000,
                ),
            ],
        );

        $data = new PurchaseReturnData(
            purchase_id: $purchase->id,
            warehouse_id: $this->warehouse->id,
            return_date: now()->toDateString(),
            note: null,
            items: $items,
        );

        $action = resolve(CreatePurchaseReturn::class);

        expect(fn () => $action->handle($data))
            ->toThrow(InvalidOperationException::class);
    });
});

describe(CompletePurchaseReturn::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->supplier = Supplier::factory()->create();
        $this->warehouse = Warehouse::factory()->create();
        $this->batch = Batch::factory()->forProduct($this->product)->create(['quantity' => 100]);
    });

    it('may complete a pending purchase return', function (): void {
        $purchase = Purchase::factory()
            ->for($this->supplier)
            ->for($this->warehouse)
            ->received()
            ->create();

        $return = PurchaseReturn::factory()
            ->forPurchase($purchase)
            ->forWarehouse($this->warehouse)
            ->pending()
            ->create();

        PurchaseReturnItem::factory()
            ->forPurchaseReturn($return)
            ->forProduct($this->product)
            ->forBatch($this->batch)
            ->create(['quantity' => 10]);

        $action = resolve(CompletePurchaseReturn::class);

        $result = $action->handle($return);

        expect($result->status)->toBe(ReturnStatusEnum::Completed);
    });

    it('deducts stock from batch when completing', function (): void {
        $purchase = Purchase::factory()
            ->for($this->supplier)
            ->for($this->warehouse)
            ->received()
            ->create();

        $return = PurchaseReturn::factory()
            ->forPurchase($purchase)
            ->forWarehouse($this->warehouse)
            ->pending()
            ->create();

        PurchaseReturnItem::factory()
            ->forPurchaseReturn($return)
            ->forProduct($this->product)
            ->forBatch($this->batch)
            ->create(['quantity' => 15]);

        $action = resolve(CompletePurchaseReturn::class);

        $action->handle($return);

        expect($this->batch->fresh()->quantity)->toBe(85);
    });

    it('throws exception when completing already completed return', function (): void {
        $purchase = Purchase::factory()
            ->for($this->supplier)
            ->for($this->warehouse)
            ->received()
            ->create();

        $return = PurchaseReturn::factory()
            ->forPurchase($purchase)
            ->forWarehouse($this->warehouse)
            ->completed()
            ->create();

        $action = resolve(CompletePurchaseReturn::class);

        expect(fn () => $action->handle($return))
            ->toThrow(StateTransitionException::class);
    });

    // Note: Testing InvalidBatchException is difficult due to foreign key constraints.
    // The action code handles the case where batch relationship is null, but we cannot
    // create a PurchaseReturnItem with a non-existent batch_id in tests.
});

describe(SaleReturn\ResolveReturnableQuantity::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->customer = Customer::factory()->create();
        $this->warehouse = Warehouse::factory()->create();
        $this->batch = Batch::factory()->forProduct($this->product)->forWarehouse($this->warehouse)->create();
    });

    it('resolves returnable quantity for sale', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->completed()
            ->create();

        SaleItem::factory()->forSale($sale)->forProduct($this->product)->forBatch($this->batch)->create([
            'quantity' => 100,
        ]);

        $action = resolve(SaleReturnResolveReturnableQuantity::class);

        $result = $action->handle($sale);

        expect($result->get($this->product->id.':'.$this->batch->id))->toBe(100);
    });

    it('subtracts already returned quantity for sale', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->completed()
            ->create();

        SaleItem::factory()->forSale($sale)->forProduct($this->product)->forBatch($this->batch)->create([
            'quantity' => 100,
        ]);

        SaleReturn::factory()
            ->forSale($sale)
            ->forWarehouse($this->warehouse)
            ->create(['status' => ReturnStatusEnum::Completed]);

        SaleReturnItem::factory()
            ->forSaleReturn(SaleReturn::query()->latest()->first())
            ->forProduct($this->product)
            ->forBatch($this->batch)
            ->create(['quantity' => 40]);

        $action = resolve(SaleReturnResolveReturnableQuantity::class);

        $result = $action->handle($sale);

        expect($result->get($this->product->id.':'.$this->batch->id))->toBe(60);
    });

    it('validates sale returnable quantity', function (): void {
        $key = $this->product->id.':'.$this->batch->id;
        $returnableMap = collect([$key => 50]);

        $items = new DataCollection(
            SaleReturnItemData::class,
            [
                new SaleReturnItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 30,
                    unit_price: 1000,
                ),
            ],
        );

        $action = resolve(SaleReturnResolveReturnableQuantity::class);

        // Should not throw any exception
        $action->validate($returnableMap, $items);

        expect($returnableMap->get($key))->toBe(50);
    });

    it('throws exception when exceeding sale returnable quantity', function (): void {
        $key = $this->product->id.':'.$this->batch->id;
        $returnableMap = collect([$key => 50]);

        $items = new DataCollection(
            SaleReturnItemData::class,
            [
                new SaleReturnItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 100,
                    unit_price: 1000,
                ),
            ],
        );

        $action = resolve(SaleReturnResolveReturnableQuantity::class);

        expect(fn () => $action->validate($returnableMap, $items))
            ->toThrow(InvalidOperationException::class, 'exceeds returnable quantity');
    });
});

describe(CreateSaleReturn::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->customer = Customer::factory()->create();
        $this->warehouse = Warehouse::factory()->create();
        $this->batch = Batch::factory()->forProduct($this->product)->forWarehouse($this->warehouse)->create();
    });

    it('may create a sale return', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->completed()
            ->create();

        SaleItem::factory()->forSale($sale)->forProduct($this->product)->forBatch($this->batch)->create([
            'quantity' => 100,
        ]);

        $items = new DataCollection(
            SaleReturnItemData::class,
            [
                new SaleReturnItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 10,
                    unit_price: 7500,
                ),
            ],
        );

        $data = new SaleReturnData(
            sale_id: $sale->id,
            warehouse_id: $this->warehouse->id,
            return_date: now(),
            note: 'Customer return',
            items: $items,
        );

        $action = resolve(CreateSaleReturn::class);

        $return = $action->handle($data);

        expect($return)->toBeInstanceOf(SaleReturn::class)
            ->and($return->sale_id)->toBe($sale->id)
            ->and($return->warehouse_id)->toBe($this->warehouse->id)
            ->and($return->status)->toBe(ReturnStatusEnum::Pending)
            ->and($return->total_amount)->toBe(75000)
            ->and($return->payment_status)->toBe(PaymentStatusEnum::Unpaid);
    });

    it('throws exception when sale is not completed', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->pending()
            ->create();

        $items = new DataCollection(
            SaleReturnItemData::class,
            [
                new SaleReturnItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 5,
                    unit_price: 1000,
                ),
            ],
        );

        $data = new SaleReturnData(
            sale_id: $sale->id,
            warehouse_id: $this->warehouse->id,
            return_date: now(),
            note: null,
            items: $items,
        );

        $action = resolve(CreateSaleReturn::class);

        expect(fn () => $action->handle($data))
            ->toThrow(InvalidOperationException::class, 'Returns can only be created for completed sales');
    });

    it('validates returnable quantity before creating sale return', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->completed()
            ->create();

        SaleItem::factory()->forSale($sale)->forProduct($this->product)->create([
            'quantity' => 50,
        ]);

        $items = new DataCollection(
            SaleReturnItemData::class,
            [
                new SaleReturnItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 100, // Exceeds sold quantity
                    unit_price: 1000,
                ),
            ],
        );

        $data = new SaleReturnData(
            sale_id: $sale->id,
            warehouse_id: $this->warehouse->id,
            return_date: now(),
            note: null,
            items: $items,
        );

        $action = resolve(CreateSaleReturn::class);

        expect(fn () => $action->handle($data))
            ->toThrow(InvalidOperationException::class);
    });
});

describe(CompleteSaleReturn::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->customer = Customer::factory()->create();
        $this->warehouse = Warehouse::factory()->create();
        $this->batch = Batch::factory()->forProduct($this->product)->create(['quantity' => 50]);
    });

    it('may complete a pending sale return', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->completed()
            ->create();

        $return = SaleReturn::factory()
            ->forSale($sale)
            ->forWarehouse($this->warehouse)
            ->pending()
            ->create();

        SaleReturnItem::factory()
            ->forSaleReturn($return)
            ->forProduct($this->product)
            ->forBatch($this->batch)
            ->create(['quantity' => 10]);

        $action = resolve(CompleteSaleReturn::class);

        $result = $action->handle($return);

        expect($result->status)->toBe(ReturnStatusEnum::Completed);
    });

    it('adds stock to batch when completing sale return', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->completed()
            ->create();

        $return = SaleReturn::factory()
            ->forSale($sale)
            ->forWarehouse($this->warehouse)
            ->pending()
            ->create();

        SaleReturnItem::factory()
            ->forSaleReturn($return)
            ->forProduct($this->product)
            ->forBatch($this->batch)
            ->create(['quantity' => 15]);

        $action = resolve(CompleteSaleReturn::class);

        $action->handle($return);

        expect($this->batch->fresh()->quantity)->toBe(65);
    });

    it('throws exception when completing already completed sale return', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->completed()
            ->create();

        $return = SaleReturn::factory()
            ->forSale($sale)
            ->forWarehouse($this->warehouse)
            ->completed()
            ->create();

        $action = resolve(CompleteSaleReturn::class);

        expect(fn () => $action->handle($return))
            ->toThrow(StateTransitionException::class);
    });

    // Note: Testing InvalidBatchException is difficult due to foreign key constraints.
    // The action code handles the case where batch relationship is null, but we cannot
    // create a SaleReturnItem with a non-existent batch_id in tests.
});
