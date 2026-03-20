<?php

declare(strict_types=1);

use App\Actions\Sale\CancelSale;
use App\Actions\Sale\CompleteSale;
use App\Actions\Sale\DeleteSale;
use App\Actions\Sale\UpdateSale;
use App\Data\Sale\SaleData;
use App\Enums\SaleStatusEnum;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Unit;
use App\Models\Warehouse;

describe(DeleteSale::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->batch = Batch::factory()->for($this->product)->create(['quantity' => 100]);
        $this->warehouse = Warehouse::factory()->create();
        $this->customer = Customer::factory()->create();
    });

    it('may delete a pending sale without payments', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->pending()->create();
        SaleItem::factory()->forSale($sale)->forProduct($this->product)->forBatch($this->batch)->create();

        $action = resolve(DeleteSale::class);

        $result = $action->handle($sale);

        expect($result)->toBeTrue()
            ->and(Sale::query()->where('id', $sale->id)->exists())->toBeFalse();
    });

    it('throws exception when deleting completed sale', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->completed()->create();

        $action = resolve(DeleteSale::class);

        expect(fn () => $action->handle($sale))->toThrow(App\Exceptions\InvalidOperationException::class);
    });

    it('throws exception when deleting sale with active payments', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->pending()->create();
        App\Models\Payment::factory()->forSale($sale)->create(['status' => App\Enums\PaymentStateEnum::Active]);

        $action = resolve(DeleteSale::class);

        expect(fn () => $action->handle($sale))->toThrow(App\Exceptions\InvalidOperationException::class);
    });
});

describe(CompleteSale::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->batch = Batch::factory()->for($this->product)->create(['quantity' => 100]);
        $this->warehouse = Warehouse::factory()->create();
        $this->customer = Customer::factory()->create();
    });

    it('may complete a pending sale and deduct stock', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->pending()->create();
        SaleItem::factory()->forSale($sale)->forProduct($this->product)->forBatch($this->batch)->withQuantity(5)->create();

        $action = resolve(CompleteSale::class);

        $result = $action->handle($sale);

        expect($result->status)->toBe(SaleStatusEnum::Completed);

        $this->batch->refresh();
        expect($this->batch->quantity)->toBe(95);
    });

    it('throws exception when completing already completed sale', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->completed()->create();

        $action = resolve(CompleteSale::class);

        expect(fn () => $action->handle($sale))->toThrow(App\Exceptions\StateTransitionException::class);
    });

    it('throws exception when completing cancelled sale', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->cancelled()->create();

        $action = resolve(CompleteSale::class);

        expect(fn () => $action->handle($sale))->toThrow(App\Exceptions\StateTransitionException::class);
    });

    it('deducts stock for multiple sale items', function (): void {
        $product2 = Product::factory()->for($this->unit)->create();
        $batch2 = Batch::factory()->for($product2)->for($this->warehouse)->create(['quantity' => 50]);

        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->pending()->create();
        SaleItem::factory()->forSale($sale)->forProduct($this->product)->forBatch($this->batch)->withQuantity(10)->create();
        SaleItem::factory()->forSale($sale)->forProduct($product2)->forBatch($batch2)->withQuantity(5)->create();

        $action = resolve(CompleteSale::class);

        $result = $action->handle($sale);

        expect($result->status)->toBe(SaleStatusEnum::Completed)
            ->and($this->batch->fresh()->quantity)->toBe(90)
            ->and($batch2->fresh()->quantity)->toBe(45);
    });

    it('loads items relationship after completion', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->pending()->create();
        SaleItem::factory()->forSale($sale)->forProduct($this->product)->forBatch($this->batch)->withQuantity(5)->create();

        $action = resolve(CompleteSale::class);

        $result = $action->handle($sale);

        expect($result->relationLoaded('items'))->toBeTrue()
            ->and($result->items)->toHaveCount(1);
    });

    it('throws exception with StateTransitionException message', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->cancelled()->create();

        $action = resolve(CompleteSale::class);

        expect(fn () => $action->handle($sale))
            ->toThrow(App\Exceptions\StateTransitionException::class, 'Invalid state transition from "cancelled" to "completed"');
    });
});

describe(CancelSale::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->batch = Batch::factory()->for($this->product)->create(['quantity' => 100]);
        $this->warehouse = Warehouse::factory()->create();
        $this->customer = Customer::factory()->create();
    });

    it('may cancel a pending sale', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->pending()->create();

        $action = resolve(CancelSale::class);

        $result = $action->handle($sale);

        expect($result->status)->toBe(SaleStatusEnum::Cancelled);
    });

    it('may cancel a completed sale and restore stock', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->completed()->create();
        SaleItem::factory()->forSale($sale)->forProduct($this->product)->forBatch($this->batch)->withQuantity(5)->create();

        $action = resolve(CancelSale::class);

        $result = $action->handle($sale);

        expect($result->status)->toBe(SaleStatusEnum::Cancelled);

        $this->batch->refresh();
        expect($this->batch->quantity)->toBe(105);
    });

    it('throws exception when cancelling cancelled sale', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->cancelled()->create();

        $action = resolve(CancelSale::class);

        expect(fn () => $action->handle($sale))->toThrow(App\Exceptions\StateTransitionException::class);
    });

    it('throws exception when cancelling sale with active payments', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->pending()->create();
        App\Models\Payment::factory()->forSale($sale)->create(['status' => App\Enums\PaymentStateEnum::Active]);

        $action = resolve(CancelSale::class);

        expect(fn () => $action->handle($sale))->toThrow(App\Exceptions\InvalidOperationException::class);
    });

    it('cancels completed sale with custom reason', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->completed()->create();
        SaleItem::factory()->forSale($sale)->forProduct($this->product)->forBatch($this->batch)->withQuantity(10)->create();

        $action = resolve(CancelSale::class);

        $result = $action->handle($sale, 'Customer requested cancellation');

        expect($result->status)->toBe(SaleStatusEnum::Cancelled);
        $this->batch->refresh();
        expect($this->batch->quantity)->toBe(110);
    });

    it('uses default cancellation note when no reason provided', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->completed()->create();
        SaleItem::factory()->forSale($sale)->forProduct($this->product)->forBatch($this->batch)->withQuantity(5)->create();

        $action = resolve(CancelSale::class);

        $result = $action->handle($sale);

        expect($result->status)->toBe(SaleStatusEnum::Cancelled);
    });

    it('does not change stock when cancelling pending sale', function (): void {
        $initialQuantity = $this->batch->quantity;
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->pending()->create();

        $action = resolve(CancelSale::class);

        $action->handle($sale);

        expect($this->batch->fresh()->quantity)->toBe($initialQuantity);
    });

    it('restores stock to correct batch for multi-item sale', function (): void {
        $product2 = Product::factory()->for($this->unit)->create();
        $batch2 = Batch::factory()->for($product2)->for($this->warehouse)->create(['quantity' => 50]);

        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->completed()->create();
        SaleItem::factory()->forSale($sale)->forProduct($this->product)->forBatch($this->batch)->withQuantity(5)->create();
        SaleItem::factory()->forSale($sale)->forProduct($product2)->forBatch($batch2)->withQuantity(10)->create();

        $action = resolve(CancelSale::class);

        $action->handle($sale);

        expect($this->batch->fresh()->quantity)->toBe(105)
            ->and($batch2->fresh()->quantity)->toBe(60);
    });

    it('throws exception when cancelling completed sale with voided payments only', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->completed()->create();
        App\Models\Payment::factory()->forSale($sale)->create(['status' => App\Enums\PaymentStateEnum::Voided]);

        $action = resolve(CancelSale::class);

        $result = $action->handle($sale);

        expect($result->status)->toBe(SaleStatusEnum::Cancelled);
    });
});

describe(UpdateSale::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->batch = Batch::factory()->for($this->product)->create(['quantity' => 100]);
        $this->warehouse = Warehouse::factory()->create();
        $this->customer = Customer::factory()->create();
    });

    it('may update a pending sale', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->pending()->create();
        SaleItem::factory()->forSale($sale)->forProduct($this->product)->forBatch($this->batch)->create();

        $action = resolve(UpdateSale::class);

        $data = SaleData::from([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => SaleStatusEnum::Pending,
            'sale_date' => now(),
            'total_amount' => 15000,
            'paid_amount' => 0,
            'change_amount' => 0,
            'note' => 'Updated note',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'batch_id' => $this->batch->id,
                    'quantity' => 10,
                    'unit_price' => 1500,
                    'unit_cost' => 750,
                ],
            ],
        ]);

        $result = $action->handle($sale, $data);

        expect($result->total_amount)->toBe(15000);
    });

    it('throws exception when updating completed sale', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->completed()->create();

        $action = resolve(UpdateSale::class);

        $data = SaleData::from([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => SaleStatusEnum::Pending,
            'sale_date' => now(),
            'total_amount' => 15000,
            'paid_amount' => 0,
            'change_amount' => 0,
            'note' => 'Updated note',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'batch_id' => $this->batch->id,
                    'quantity' => 10,
                    'unit_price' => 1500,
                    'unit_cost' => 750,
                ],
            ],
        ]);

        expect(fn () => $action->handle($sale, $data))->toThrow(App\Exceptions\InvalidOperationException::class);
    });

    it('throws exception when updating cancelled sale', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->cancelled()->create();

        $action = resolve(UpdateSale::class);

        $data = SaleData::from([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => SaleStatusEnum::Pending,
            'sale_date' => now(),
            'total_amount' => 15000,
            'paid_amount' => 0,
            'change_amount' => 0,
            'note' => 'Updated note',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'batch_id' => $this->batch->id,
                    'quantity' => 10,
                    'unit_price' => 1500,
                    'unit_cost' => 750,
                ],
            ],
        ]);

        expect(fn () => $action->handle($sale, $data))->toThrow(App\Exceptions\InvalidOperationException::class);
    });

    it('updates payment status when total amount changes', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->pending()->create([
            'total_amount' => 10000,
            'paid_amount' => 5000,
            'payment_status' => App\Enums\PaymentStatusEnum::Partial,
        ]);
        SaleItem::factory()->forSale($sale)->forProduct($this->product)->forBatch($this->batch)->create();

        $action = resolve(UpdateSale::class);

        $data = SaleData::from([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => SaleStatusEnum::Pending,
            'sale_date' => now(),
            'total_amount' => 15000,
            'paid_amount' => 5000,
            'change_amount' => 0,
            'note' => null,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'batch_id' => $this->batch->id,
                    'quantity' => 10,
                    'unit_price' => 1500,
                    'unit_cost' => 750,
                ],
            ],
        ]);

        $result = $action->handle($sale, $data);

        expect($result->total_amount)->toBe(15000);
    });

    it('allows increasing total amount without affecting payment status logic', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->pending()->create([
            'total_amount' => 10000,
            'paid_amount' => 0,
            'payment_status' => App\Enums\PaymentStatusEnum::Unpaid,
        ]);
        SaleItem::factory()->forSale($sale)->forProduct($this->product)->forBatch($this->batch)->create();

        $action = resolve(UpdateSale::class);

        $data = SaleData::from([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => SaleStatusEnum::Pending,
            'sale_date' => now(),
            'total_amount' => 15000,
            'paid_amount' => 0,
            'change_amount' => 0,
            'note' => 'Increased total',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'batch_id' => $this->batch->id,
                    'quantity' => 10,
                    'unit_price' => 1500,
                    'unit_cost' => 750,
                ],
            ],
        ]);

        $result = $action->handle($sale, $data);

        expect($result->total_amount)->toBe(15000);
    });

    it('updates customer when provided', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->pending()->create();
        SaleItem::factory()->forSale($sale)->forProduct($this->product)->forBatch($this->batch)->create();
        $newCustomer = Customer::factory()->create();

        $action = resolve(UpdateSale::class);

        $data = SaleData::from([
            'customer_id' => $newCustomer->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => SaleStatusEnum::Pending,
            'sale_date' => now(),
            'total_amount' => 15000,
            'paid_amount' => 0,
            'change_amount' => 0,
            'note' => null,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'batch_id' => $this->batch->id,
                    'quantity' => 10,
                    'unit_price' => 1500,
                    'unit_cost' => 750,
                ],
            ],
        ]);

        $result = $action->handle($sale, $data);

        expect($result->customer_id)->toBe($newCustomer->id);
    });

    it('updates warehouse when provided', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->pending()->create();
        SaleItem::factory()->forSale($sale)->forProduct($this->product)->forBatch($this->batch)->create();
        $newWarehouse = Warehouse::factory()->create();

        $action = resolve(UpdateSale::class);

        $data = SaleData::from([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $newWarehouse->id,
            'status' => SaleStatusEnum::Pending,
            'sale_date' => now(),
            'total_amount' => 15000,
            'paid_amount' => 0,
            'change_amount' => 0,
            'note' => null,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'batch_id' => $this->batch->id,
                    'quantity' => 10,
                    'unit_price' => 1500,
                    'unit_cost' => 750,
                ],
            ],
        ]);

        $result = $action->handle($sale, $data);

        expect($result->warehouse_id)->toBe($newWarehouse->id);
    });

    it('deletes old items and creates new ones', function (): void {
        $sale = Sale::factory()->for($this->warehouse)->for($this->customer)->pending()->create();
        SaleItem::factory()->forSale($sale)->forProduct($this->product)->forBatch($this->batch)->create();
        $product2 = Product::factory()->for($this->unit)->create();
        $batch2 = Batch::factory()->for($product2)->for($this->warehouse)->create();

        $action = resolve(UpdateSale::class);

        $data = SaleData::from([
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => SaleStatusEnum::Pending,
            'sale_date' => now(),
            'total_amount' => 10000,
            'paid_amount' => 0,
            'change_amount' => 0,
            'note' => null,
            'items' => [
                [
                    'product_id' => $product2->id,
                    'batch_id' => $batch2->id,
                    'quantity' => 5,
                    'unit_price' => 2000,
                    'unit_cost' => 1000,
                ],
            ],
        ]);

        $result = $action->handle($sale, $data);

        expect($result->items)->toHaveCount(1)
            ->and($result->items->first()->product_id)->toBe($product2->id);
    });

});
