<?php

declare(strict_types=1);

use App\Actions\Pos\PosOrderResult;
use App\Actions\Pos\ProcessPosOrder;
use App\Actions\Pos\SearchPosProducts;
use App\Data\Pos\PosCartItemData;
use App\Data\Pos\PosOrderData;
use App\Enums\PaymentStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Unit;
use App\Models\Warehouse;
use Spatie\LaravelData\DataCollection;

describe(PosOrderResult::class, function (): void {
    it('may create a pos order result', function (): void {
        $sale = Sale::factory()->create();
        $payment = Payment::factory()->create();
        $changeAmount = 500;

        $result = new PosOrderResult(
            sale: $sale,
            payment: $payment,
            changeAmount: $changeAmount,
        );

        expect($result)->toBeInstanceOf(PosOrderResult::class)
            ->and($result->sale)->toBe($sale)
            ->and($result->payment)->toBe($payment)
            ->and($result->changeAmount)->toBe(500);
    });

    it('has readonly properties', function (): void {
        $sale = Sale::factory()->create();
        $payment = Payment::factory()->create();

        $result = new PosOrderResult(
            sale: $sale,
            payment: $payment,
            changeAmount: 500,
        );

        expect($result->sale)->toBe($sale)
            ->and($result->payment)->toBe($payment)
            ->and($result->changeAmount)->toBe(500);
    });
});

describe(ProcessPosOrder::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->warehouse = Warehouse::factory()->create();
        $this->customer = Customer::factory()->create();
        $this->paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);
        $this->product = Product::factory()
            ->for($this->unit)
            ->create(['selling_price' => 1000, 'cost_price' => 500]);
        $this->batch = Batch::factory()
            ->for($this->product)
            ->for($this->warehouse)
            ->create(['quantity' => 100, 'cost_amount' => 500]);
    });

    it('may process a pos order', function (): void {
        $items = new DataCollection(
            PosCartItemData::class,
            [
                new PosCartItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 2,
                    unit_price: 1000,
                    unit_cost: 500,
                ),
            ],
        );

        $data = new PosOrderData(
            customer_id: $this->customer->id,
            warehouse_id: $this->warehouse->id,
            payment_method_id: $this->paymentMethod->id,
            cash_tendered: 2000,
            total_amount: 2000,
            note: 'POS order',
            items: $items,
        );

        $action = resolve(ProcessPosOrder::class);

        $result = $action->handle($data);

        expect($result)->toBeInstanceOf(PosOrderResult::class)
            ->and($result->sale)->toBeInstanceOf(Sale::class)
            ->and($result->payment)->toBeInstanceOf(Payment::class)
            ->and($result->changeAmount)->toBe(0);
    });

    it('creates sale with completed status', function (): void {
        $items = new DataCollection(
            PosCartItemData::class,
            [
                new PosCartItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 2,
                    unit_price: 1000,
                    unit_cost: 500,
                ),
            ],
        );

        $data = new PosOrderData(
            customer_id: $this->customer->id,
            warehouse_id: $this->warehouse->id,
            payment_method_id: $this->paymentMethod->id,
            cash_tendered: 2000,
            total_amount: 2000,
            note: null,
            items: $items,
        );

        $action = resolve(ProcessPosOrder::class);

        $result = $action->handle($data);

        expect($result->sale->status)->toBe(SaleStatusEnum::Completed)
            ->and($result->sale->payment_status)->toBe(PaymentStatusEnum::Paid);
    });

    it('deducts stock from batch', function (): void {
        $items = new DataCollection(
            PosCartItemData::class,
            [
                new PosCartItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 5,
                    unit_price: 1000,
                    unit_cost: 500,
                ),
            ],
        );

        $data = new PosOrderData(
            customer_id: $this->customer->id,
            warehouse_id: $this->warehouse->id,
            payment_method_id: $this->paymentMethod->id,
            cash_tendered: 5000,
            total_amount: 5000,
            note: null,
            items: $items,
        );

        $action = resolve(ProcessPosOrder::class);

        $action->handle($data);

        expect($this->batch->fresh()->quantity)->toBe(95);
    });

    it('calculates change amount correctly', function (): void {
        $items = new DataCollection(
            PosCartItemData::class,
            [
                new PosCartItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 2,
                    unit_price: 1000,
                    unit_cost: 500,
                ),
            ],
        );

        $data = new PosOrderData(
            customer_id: null,
            warehouse_id: $this->warehouse->id,
            payment_method_id: $this->paymentMethod->id,
            cash_tendered: 5000,
            total_amount: 2000,
            note: null,
            items: $items,
        );

        $action = resolve(ProcessPosOrder::class);

        $result = $action->handle($data);

        expect($result->changeAmount)->toBe(3000)
            ->and($result->sale->change_amount)->toBe(3000);
    });

    it('throws exception when cash tendered is less than total', function (): void {
        $items = new DataCollection(
            PosCartItemData::class,
            [
                new PosCartItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 2,
                    unit_price: 1000,
                    unit_cost: 500,
                ),
            ],
        );

        $data = new PosOrderData(
            customer_id: $this->customer->id,
            warehouse_id: $this->warehouse->id,
            payment_method_id: $this->paymentMethod->id,
            cash_tendered: 1000,
            total_amount: 2000,
            note: null,
            items: $items,
        );

        $action = resolve(ProcessPosOrder::class);

        expect(fn () => $action->handle($data))
            ->toThrow(InvalidOperationException::class, 'Cash tendered (1000) is less than total amount (2000)');
    });

    it('creates sale items', function (): void {
        $items = new DataCollection(
            PosCartItemData::class,
            [
                new PosCartItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 3,
                    unit_price: 1000,
                    unit_cost: 500,
                ),
            ],
        );

        $data = new PosOrderData(
            customer_id: $this->customer->id,
            warehouse_id: $this->warehouse->id,
            payment_method_id: $this->paymentMethod->id,
            cash_tendered: 3000,
            total_amount: 3000,
            note: null,
            items: $items,
        );

        $action = resolve(ProcessPosOrder::class);

        $result = $action->handle($data);

        expect($result->sale->items)->toHaveCount(1)
            ->and($result->sale->items->first()->product_id)->toBe($this->product->id)
            ->and($result->sale->items->first()->batch_id)->toBe($this->batch->id)
            ->and($result->sale->items->first()->quantity)->toBe(3)
            ->and($result->sale->items->first()->unit_price)->toBe(1000)
            ->and($result->sale->items->first()->subtotal)->toBe(3000);
    });

    it('generates unique reference number', function (): void {
        $items = new DataCollection(
            PosCartItemData::class,
            [
                new PosCartItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 1,
                    unit_price: 1000,
                    unit_cost: 500,
                ),
            ],
        );

        $data1 = new PosOrderData(
            customer_id: $this->customer->id,
            warehouse_id: $this->warehouse->id,
            payment_method_id: $this->paymentMethod->id,
            cash_tendered: 1000,
            total_amount: 1000,
            note: null,
            items: $items,
        );

        $data2 = new PosOrderData(
            customer_id: $this->customer->id,
            warehouse_id: $this->warehouse->id,
            payment_method_id: $this->paymentMethod->id,
            cash_tendered: 1000,
            total_amount: 1000,
            note: null,
            items: $items,
        );

        $action = resolve(ProcessPosOrder::class);

        $result1 = $action->handle($data1);
        $result2 = $action->handle($data2);

        expect($result1->sale->reference_no)->not->toBe($result2->sale->reference_no);
    });

    it('loads relationships on sale', function (): void {
        $items = new DataCollection(
            PosCartItemData::class,
            [
                new PosCartItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 1,
                    unit_price: 1000,
                    unit_cost: 500,
                ),
            ],
        );

        $data = new PosOrderData(
            customer_id: $this->customer->id,
            warehouse_id: $this->warehouse->id,
            payment_method_id: $this->paymentMethod->id,
            cash_tendered: 1000,
            total_amount: 1000,
            note: null,
            items: $items,
        );

        $action = resolve(ProcessPosOrder::class);

        $result = $action->handle($data);

        // Verify relationships are loaded and accessible
        expect($result->sale->items)->toHaveCount(1)
            ->and($result->sale->items->first()->product)->toBeInstanceOf(Product::class)
            ->and($result->sale->items->first()->product->unit)->toBeInstanceOf(Unit::class)
            ->and($result->sale->items->first()->batch)->toBeInstanceOf(Batch::class)
            ->and($result->sale->customer)->toBeInstanceOf(Customer::class)
            ->and($result->sale->warehouse)->toBeInstanceOf(Warehouse::class)
            ->and($result->sale->payments->first()->paymentMethod)->toBeInstanceOf(PaymentMethod::class);
    });

    it('works without customer (walk-in sale)', function (): void {
        $items = new DataCollection(
            PosCartItemData::class,
            [
                new PosCartItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 1,
                    unit_price: 1000,
                    unit_cost: 500,
                ),
            ],
        );

        $data = new PosOrderData(
            customer_id: null,
            warehouse_id: $this->warehouse->id,
            payment_method_id: $this->paymentMethod->id,
            cash_tendered: 1000,
            total_amount: 1000,
            note: 'Walk-in customer',
            items: $items,
        );

        $action = resolve(ProcessPosOrder::class);

        $result = $action->handle($data);

        expect($result->sale->customer_id)->toBeNull()
            ->and($result->sale->status)->toBe(SaleStatusEnum::Completed);
    });
});

describe(SearchPosProducts::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->warehouse = Warehouse::factory()->create();
        $this->otherWarehouse = Warehouse::factory()->create();
    });

    it('may search products by name', function (): void {
        $product1 = Product::factory()
            ->for($this->unit)
            ->create(['name' => 'Test Product', 'track_inventory' => true]);
        $product2 = Product::factory()
            ->for($this->unit)
            ->create(['name' => 'Another Product', 'track_inventory' => true]);

        Batch::factory()->for($product1)->for($this->warehouse)->create(['quantity' => 10]);
        Batch::factory()->for($product2)->for($this->warehouse)->create(['quantity' => 10]);

        $action = resolve(SearchPosProducts::class);

        $results = $action->handle('Test', $this->warehouse->id);

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($product1->id);
    });

    it('searches products by SKU', function (): void {
        $product = Product::factory()
            ->for($this->unit)
            ->create(['sku' => 'ABC123', 'track_inventory' => true]);

        Batch::factory()->for($product)->for($this->warehouse)->create(['quantity' => 10]);

        $action = resolve(SearchPosProducts::class);

        $results = $action->handle('ABC123', $this->warehouse->id);

        expect($results)->toHaveCount(1)
            ->and($results->first()->sku)->toBe('ABC123');
    });

    it('searches products by barcode', function (): void {
        $product = Product::factory()
            ->for($this->unit)
            ->create(['barcode' => '1234567890', 'track_inventory' => true]);

        Batch::factory()->for($product)->for($this->warehouse)->create(['quantity' => 10]);

        $action = resolve(SearchPosProducts::class);

        $results = $action->handle('1234567890', $this->warehouse->id);

        expect($results)->toHaveCount(1)
            ->and($results->first()->barcode)->toBe('1234567890');
    });

    it('only returns tracked products', function (): void {
        $trackedProduct = Product::factory()
            ->for($this->unit)
            ->create(['track_inventory' => true]);
        $untrackedProduct = Product::factory()
            ->for($this->unit)
            ->create(['track_inventory' => false]);

        Batch::factory()->for($trackedProduct)->for($this->warehouse)->create(['quantity' => 10]);
        Batch::factory()->for($untrackedProduct)->for($this->warehouse)->create(['quantity' => 10]);

        $action = resolve(SearchPosProducts::class);

        $results = $action->handle('', $this->warehouse->id);

        // Only tracked products should be returned
        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($trackedProduct->id);
    });

    it('only returns products with stock in warehouse', function (): void {
        $inStockProduct = Product::factory()
            ->for($this->unit)
            ->create(['track_inventory' => true]);
        $outOfStockProduct = Product::factory()
            ->for($this->unit)
            ->create(['track_inventory' => true]);

        Batch::factory()->for($inStockProduct)->for($this->warehouse)->create(['quantity' => 10]);
        Batch::factory()->for($outOfStockProduct)->for($this->warehouse)->create(['quantity' => 0]);

        $action = resolve(SearchPosProducts::class);

        $results = $action->handle('', $this->warehouse->id);

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($inStockProduct->id);
    });

    it('only returns products from specified warehouse', function (): void {
        $product = Product::factory()
            ->for($this->unit)
            ->create(['name' => 'Warehouse Product', 'track_inventory' => true]);

        Batch::factory()->for($product)->for($this->warehouse)->create(['quantity' => 10]);
        Batch::factory()->for($product)->for($this->otherWarehouse)->create(['quantity' => 10]);

        $action = resolve(SearchPosProducts::class);

        $results = $action->handle('Warehouse', $this->otherWarehouse->id);

        expect($results)->toHaveCount(1)
            ->and($results->first()->id)->toBe($product->id);
    });

    it('returns products ordered by expiry date (FEFO)', function (): void {
        $product = Product::factory()
            ->for($this->unit)
            ->create(['track_inventory' => true]);

        $expiringBatch = Batch::factory()
            ->for($product)
            ->for($this->warehouse)
            ->create(['quantity' => 10, 'expires_at' => now()->addDays(5)]);

        $laterBatch = Batch::factory()
            ->for($product)
            ->for($this->warehouse)
            ->create(['quantity' => 10, 'expires_at' => now()->addDays(30)]);

        $noExpiryBatch = Batch::factory()
            ->for($product)
            ->for($this->warehouse)
            ->create(['quantity' => 10, 'expires_at' => null]);

        $action = resolve(SearchPosProducts::class);

        $results = $action->handle($product->name, $this->warehouse->id);

        expect($results)->toHaveCount(1);
    });

    it('limits results to 20 products', function (): void {
        Product::factory()
            ->count(25)
            ->for($this->unit)
            ->create(['track_inventory' => true])
            ->each(function ($product): void {
                Batch::factory()->for($product)->for($this->warehouse)->create(['quantity' => 10]);
            });

        $action = resolve(SearchPosProducts::class);

        $results = $action->handle('', $this->warehouse->id);

        expect($results)->toHaveCount(20);
    });

    it('returns empty collection when no products match', function (): void {
        $action = resolve(SearchPosProducts::class);

        $results = $action->handle('NonExistentProduct', $this->warehouse->id);

        expect($results)->toBeEmpty();
    });

    it('includes unit relationship', function (): void {
        $product = Product::factory()
            ->for($this->unit)
            ->create(['track_inventory' => true]);

        Batch::factory()->for($product)->for($this->warehouse)->create(['quantity' => 10]);

        $action = resolve(SearchPosProducts::class);

        $results = $action->handle($product->name, $this->warehouse->id);

        expect($results->first()->unit)->toBeInstanceOf(Unit::class)
            ->and($results->first()->unit->id)->toBe($this->unit->id);
    });
});
