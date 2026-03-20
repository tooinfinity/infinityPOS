<?php

declare(strict_types=1);

use App\Actions\Sale\CreateSale;
use App\Data\Sale\SaleData;
use App\Data\Sale\SaleItemData;
use App\Enums\SaleStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Unit;
use App\Models\Warehouse;
use Spatie\LaravelData\DataCollection;

describe(CreateSale::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create(['selling_price' => 1500, 'cost_price' => 750]);
        $this->batch = Batch::factory()->for($this->product)->create(['quantity' => 100]);
        $this->warehouse = Warehouse::factory()->create();
        $this->customer = Customer::factory()->create();
    });

    it('may create a pending sale', function (): void {
        $items = new DataCollection(
            SaleItemData::class,
            [
                new SaleItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 10,
                    unit_price: 1500,
                    unit_cost: 750,
                ),
            ],
        );

        $data = new SaleData(
            customer_id: $this->customer->id,
            warehouse_id: $this->warehouse->id,
            status: SaleStatusEnum::Pending,
            sale_date: now(),
            total_amount: 15000,
            note: 'Test sale',
            items: $items,
        );

        $action = resolve(CreateSale::class);

        $sale = $action->handle($data);

        expect($sale)->toBeInstanceOf(Sale::class)
            ->and($sale->customer_id)->toBe($this->customer->id)
            ->and($sale->warehouse_id)->toBe($this->warehouse->id)
            ->and($sale->status)->toBe(SaleStatusEnum::Pending)
            ->and($sale->total_amount)->toBe(15000)
            ->and($sale->payment_status)->toBe(App\Enums\PaymentStatusEnum::Unpaid);
    });

    it('generates unique reference number', function (): void {
        $items = new DataCollection(
            SaleItemData::class,
            [
                new SaleItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 1,
                    unit_price: 1500,
                    unit_cost: 750,
                ),
            ],
        );

        $data1 = new SaleData(
            customer_id: $this->customer->id,
            warehouse_id: $this->warehouse->id,
            status: SaleStatusEnum::Pending,
            sale_date: now(),
            total_amount: 1500,
            note: null,
            items: $items,
        );

        $data2 = new SaleData(
            customer_id: $this->customer->id,
            warehouse_id: $this->warehouse->id,
            status: SaleStatusEnum::Pending,
            sale_date: now(),
            total_amount: 1500,
            note: null,
            items: $items,
        );

        $action = resolve(CreateSale::class);

        $sale1 = $action->handle($data1);
        $sale2 = $action->handle($data2);

        expect($sale1->reference_no)->not->toBe($sale2->reference_no);
    });

    it('creates sale items', function (): void {
        $items = new DataCollection(
            SaleItemData::class,
            [
                new SaleItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 5,
                    unit_price: 1500,
                    unit_cost: 750,
                ),
            ],
        );

        $data = new SaleData(
            customer_id: $this->customer->id,
            warehouse_id: $this->warehouse->id,
            status: SaleStatusEnum::Pending,
            sale_date: now(),
            total_amount: 7500,
            note: null,
            items: $items,
        );

        $action = resolve(CreateSale::class);

        $sale = $action->handle($data);

        expect($sale->items)->toHaveCount(1)
            ->and($sale->items->first()->product_id)->toBe($this->product->id)
            ->and($sale->items->first()->batch_id)->toBe($this->batch->id)
            ->and($sale->items->first()->quantity)->toBe(5)
            ->and($sale->items->first()->unit_price)->toBe(1500)
            ->and($sale->items->first()->subtotal)->toBe(7500);
    });

    it('creates completed sale and deducts stock', function (): void {
        $items = new DataCollection(
            SaleItemData::class,
            [
                new SaleItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 10,
                    unit_price: 1500,
                    unit_cost: 750,
                ),
            ],
        );

        $data = new SaleData(
            customer_id: $this->customer->id,
            warehouse_id: $this->warehouse->id,
            status: SaleStatusEnum::Completed,
            sale_date: now(),
            total_amount: 15000,
            note: null,
            items: $items,
        );

        $action = resolve(CreateSale::class);

        $sale = $action->handle($data);

        expect($sale->status)->toBe(SaleStatusEnum::Completed);

        $this->batch->refresh();
        expect($this->batch->quantity)->toBe(90);
    });

    it('records stock movement when completing sale', function (): void {
        $items = new DataCollection(
            SaleItemData::class,
            [
                new SaleItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 5,
                    unit_price: 1500,
                    unit_cost: 750,
                ),
            ],
        );

        $data = new SaleData(
            customer_id: $this->customer->id,
            warehouse_id: $this->warehouse->id,
            status: SaleStatusEnum::Completed,
            sale_date: now(),
            total_amount: 7500,
            note: null,
            items: $items,
        );

        $action = resolve(CreateSale::class);

        $action->handle($data);

        expect($this->batch->stockMovements()->count())->toBe(1)
            ->and($this->batch->stockMovements()->first()->type)->toBe(StockMovementTypeEnum::Out);
    });

    it('creates sale with partial payment', function (): void {
        $items = new DataCollection(
            SaleItemData::class,
            [
                new SaleItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 10,
                    unit_price: 1500,
                    unit_cost: 750,
                ),
            ],
        );

        $data = new SaleData(
            customer_id: $this->customer->id,
            warehouse_id: $this->warehouse->id,
            status: SaleStatusEnum::Pending,
            sale_date: now(),
            total_amount: 15000,
            note: null,
            items: $items,
        );

        $action = resolve(CreateSale::class);

        $sale = $action->handle($data);

        expect($sale->paid_amount)->toBe(0)
            ->and($sale->payment_status)->toBe(App\Enums\PaymentStatusEnum::Unpaid);
    });

    it('marks sale as paid when full payment is made', function (): void {
        $items = new DataCollection(
            SaleItemData::class,
            [
                new SaleItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 10,
                    unit_price: 1500,
                    unit_cost: 750,
                ),
            ],
        );

        $data = new SaleData(
            customer_id: $this->customer->id,
            warehouse_id: $this->warehouse->id,
            status: SaleStatusEnum::Pending,
            sale_date: now(),
            total_amount: 15000,
            note: null,
            items: $items,
        );

        $action = resolve(CreateSale::class);

        $sale = $action->handle($data);

        expect($sale->paid_amount)->toBe(0)
            ->and($sale->payment_status)->toBe(App\Enums\PaymentStatusEnum::Unpaid);
    });

    it('loads relationships on sale', function (): void {
        $items = new DataCollection(
            SaleItemData::class,
            [
                new SaleItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 1,
                    unit_price: 1500,
                    unit_cost: 750,
                ),
            ],
        );

        $data = new SaleData(
            customer_id: $this->customer->id,
            warehouse_id: $this->warehouse->id,
            status: SaleStatusEnum::Pending,
            sale_date: now(),
            total_amount: 1500,
            note: null,
            items: $items,
        );

        $action = resolve(CreateSale::class);

        $sale = $action->handle($data);

        expect($sale->relationLoaded('items'))->toBeTrue()
            ->and($sale->relationLoaded('customer'))->toBeTrue()
            ->and($sale->relationLoaded('warehouse'))->toBeTrue()
            ->and($sale->items->first()->relationLoaded('product'))->toBeTrue()
            ->and($sale->items->first()->relationLoaded('batch'))->toBeTrue();
    });

    it('creates sale without customer (walk-in)', function (): void {
        $items = new DataCollection(
            SaleItemData::class,
            [
                new SaleItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 1,
                    unit_price: 1500,
                    unit_cost: 750,
                ),
            ],
        );

        $data = new SaleData(
            customer_id: null,
            warehouse_id: $this->warehouse->id,
            status: SaleStatusEnum::Pending,
            sale_date: now(),
            total_amount: 1500,
            note: 'Walk-in customer',
            items: $items,
        );

        $action = resolve(CreateSale::class);

        $sale = $action->handle($data);

        expect($sale->customer_id)->toBeNull()
            ->and($sale->note)->toBe('Walk-in customer');
    });

    it('throws exception when creating completed sale with insufficient stock', function (): void {
        $items = new DataCollection(
            SaleItemData::class,
            [
                new SaleItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 200, // More than batch has (100)
                    unit_price: 1500,
                    unit_cost: 750,
                ),
            ],
        );

        $data = new SaleData(
            customer_id: $this->customer->id,
            warehouse_id: $this->warehouse->id,
            status: SaleStatusEnum::Completed,
            sale_date: now(),
            total_amount: 300000,
            note: null,
            items: $items,
        );

        $action = resolve(CreateSale::class);

        expect(fn () => $action->handle($data))
            ->toThrow(App\Exceptions\InsufficientStockException::class);
    });

    it('creates completed sale with multiple items and deducts stock from each', function (): void {
        $product2 = Product::factory()->for($this->unit)->create(['selling_price' => 2000, 'cost_price' => 1000]);
        $batch2 = Batch::factory()->for($product2)->create(['quantity' => 50]);

        $items = new DataCollection(
            SaleItemData::class,
            [
                new SaleItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 10,
                    unit_price: 1500,
                    unit_cost: 750,
                ),
                new SaleItemData(
                    product_id: $product2->id,
                    batch_id: $batch2->id,
                    quantity: 5,
                    unit_price: 2000,
                    unit_cost: 1000,
                ),
            ],
        );

        $data = new SaleData(
            customer_id: $this->customer->id,
            warehouse_id: $this->warehouse->id,
            status: SaleStatusEnum::Completed,
            sale_date: now(),
            total_amount: 25000,
            note: 'Multiple items sale',
            items: $items,
        );

        $action = resolve(CreateSale::class);

        $sale = $action->handle($data);

        expect($sale->status)->toBe(SaleStatusEnum::Completed)
            ->and($sale->items)->toHaveCount(2)
            ->and($this->batch->fresh()->quantity)->toBe(90)
            ->and($batch2->fresh()->quantity)->toBe(45);
    });

    it('sets user_id from authenticated user', function (): void {
        $items = new DataCollection(
            SaleItemData::class,
            [
                new SaleItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 1,
                    unit_price: 1500,
                    unit_cost: 750,
                ),
            ],
        );

        $data = new SaleData(
            customer_id: $this->customer->id,
            warehouse_id: $this->warehouse->id,
            status: SaleStatusEnum::Pending,
            sale_date: now(),
            total_amount: 1500,
            note: null,
            items: $items,
        );

        $action = resolve(CreateSale::class);

        $sale = $action->handle($data);

        expect($sale->user_id)->toBe(auth()->id());
    });

    it('calculates subtotal as quantity times unit_price', function (): void {
        $items = new DataCollection(
            SaleItemData::class,
            [
                new SaleItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 7,
                    unit_price: 1500,
                    unit_cost: 750,
                ),
            ],
        );

        $data = new SaleData(
            customer_id: $this->customer->id,
            warehouse_id: $this->warehouse->id,
            status: SaleStatusEnum::Pending,
            sale_date: now(),
            total_amount: 10500,
            note: null,
            items: $items,
        );

        $action = resolve(CreateSale::class);

        $sale = $action->handle($data);

        expect($sale->items->first()->subtotal)->toBe(10500);
    });

    it('works within a transaction', function (): void {
        $items = new DataCollection(
            SaleItemData::class,
            [
                new SaleItemData(
                    product_id: $this->product->id,
                    batch_id: $this->batch->id,
                    quantity: 5,
                    unit_price: 1500,
                    unit_cost: 750,
                ),
            ],
        );

        $data = new SaleData(
            customer_id: $this->customer->id,
            warehouse_id: $this->warehouse->id,
            status: SaleStatusEnum::Pending,
            sale_date: now(),
            total_amount: 7500,
            note: null,
            items: $items,
        );

        $action = resolve(CreateSale::class);

        Illuminate\Support\Facades\DB::beginTransaction();

        try {
            $sale = $action->handle($data);
            throw new Exception('Force rollback');
        } catch (Exception) {
            Illuminate\Support\Facades\DB::rollBack();
        }

        expect(Sale::query()->count())->toBe(0);
    });
});
