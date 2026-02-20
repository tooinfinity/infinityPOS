<?php

declare(strict_types=1);

use App\Actions\Sale\QuickSaleAction;
use App\Data\Sale\QuickSaleData;
use App\Data\Sale\SaleItemData;
use App\Enums\PaymentStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\Models\StockMovement;
use Spatie\LaravelData\DataCollection;

$paymentMethod = null;

beforeEach(function () use (&$paymentMethod): void {
    $paymentMethod = PaymentMethod::factory()->create();
});

it('creates completed sale in one transaction', function () use (&$paymentMethod): void {
    $batch = Batch::factory()->withQuantity(100)->create();
    $customer = Customer::factory()->create();

    $action = resolve(QuickSaleAction::class);

    $items = new DataCollection(SaleItemData::class, [
        new SaleItemData(
            product_id: $batch->product_id,
            batch_id: $batch->id,
            quantity: 10,
            unit_price: 500,
            unit_cost: 300,
        ),
    ]);

    $data = new QuickSaleData(
        customer_id: $customer->id,
        warehouse_id: $batch->warehouse_id,
        user_id: null,
        payment_method_id: $paymentMethod->id,
        sale_date: now(),
        paid_amount: 5000,
        note: 'Quick sale',
        items: $items,
    );

    $sale = $action->handle($data);

    expect($sale)
        ->toBeInstanceOf(Sale::class)
        ->and($sale->status)->toBe(SaleStatusEnum::Completed)
        ->and($sale->total_amount)->toBe(5000);
});

it('deducts stock immediately', function () use (&$paymentMethod): void {
    $batch = Batch::factory()->withQuantity(100)->create();
    $customer = Customer::factory()->create();

    $action = resolve(QuickSaleAction::class);

    $items = new DataCollection(SaleItemData::class, [
        new SaleItemData(
            product_id: $batch->product_id,
            batch_id: $batch->id,
            quantity: 25,
            unit_price: 500,
            unit_cost: 300,
        ),
    ]);

    $data = new QuickSaleData(
        customer_id: $customer->id,
        warehouse_id: $batch->warehouse_id,
        user_id: null,
        payment_method_id: $paymentMethod->id,
        sale_date: now(),
        paid_amount: 12500,
        note: null,
        items: $items,
    );

    $action->handle($data);

    expect($batch->fresh()->quantity)->toBe(75);
});

it('records payment when paid_amount > 0', function () use (&$paymentMethod): void {
    $batch = Batch::factory()->withQuantity(100)->create();
    $customer = Customer::factory()->create();

    $action = resolve(QuickSaleAction::class);

    $items = new DataCollection(SaleItemData::class, [
        new SaleItemData(
            product_id: $batch->product_id,
            batch_id: $batch->id,
            quantity: 10,
            unit_price: 500,
            unit_cost: 300,
        ),
    ]);

    $data = new QuickSaleData(
        customer_id: $customer->id,
        warehouse_id: $batch->warehouse_id,
        user_id: null,
        payment_method_id: $paymentMethod->id,
        sale_date: now(),
        paid_amount: 5000,
        note: null,
        items: $items,
    );

    $sale = $action->handle($data);

    expect(Payment::query()->where('payable_type', Sale::class)->where('payable_id', $sale->id)->exists())->toBeTrue();
});

it('handles exact payment', function () use (&$paymentMethod): void {
    $batch = Batch::factory()->withQuantity(100)->create();
    $customer = Customer::factory()->create();

    $action = resolve(QuickSaleAction::class);

    $items = new DataCollection(SaleItemData::class, [
        new SaleItemData(
            product_id: $batch->product_id,
            batch_id: $batch->id,
            quantity: 10,
            unit_price: 500,
            unit_cost: 300,
        ),
    ]);

    $data = new QuickSaleData(
        customer_id: $customer->id,
        warehouse_id: $batch->warehouse_id,
        user_id: null,
        payment_method_id: $paymentMethod->id,
        sale_date: now(),
        paid_amount: 5000,
        note: null,
        items: $items,
    );

    $sale = $action->handle($data);

    expect($sale->payment_status)->toBe(PaymentStatusEnum::Paid)
        ->and($sale->paid_amount)->toBe(5000)
        ->and($sale->change_amount)->toBe(0);
});

it('handles overpayment with change', function () use (&$paymentMethod): void {
    $batch = Batch::factory()->withQuantity(100)->create();
    $customer = Customer::factory()->create();

    $action = resolve(QuickSaleAction::class);

    $items = new DataCollection(SaleItemData::class, [
        new SaleItemData(
            product_id: $batch->product_id,
            batch_id: $batch->id,
            quantity: 10,
            unit_price: 500,
            unit_cost: 300,
        ),
    ]);

    $data = new QuickSaleData(
        customer_id: $customer->id,
        warehouse_id: $batch->warehouse_id,
        user_id: null,
        payment_method_id: $paymentMethod->id,
        sale_date: now(),
        paid_amount: 6000,
        note: null,
        items: $items,
    );

    $sale = $action->handle($data);

    expect($sale->paid_amount)->toBe(5000)
        ->and($sale->change_amount)->toBe(1000);
});

it('creates stock movements', function () use (&$paymentMethod): void {
    $batch = Batch::factory()->withQuantity(100)->create();
    $customer = Customer::factory()->create();

    $action = resolve(QuickSaleAction::class);

    $items = new DataCollection(SaleItemData::class, [
        new SaleItemData(
            product_id: $batch->product_id,
            batch_id: $batch->id,
            quantity: 20,
            unit_price: 500,
            unit_cost: 300,
        ),
    ]);

    $data = new QuickSaleData(
        customer_id: $customer->id,
        warehouse_id: $batch->warehouse_id,
        user_id: null,
        payment_method_id: $paymentMethod->id,
        sale_date: now(),
        paid_amount: 10000,
        note: null,
        items: $items,
    );

    $sale = $action->handle($data);

    $movements = StockMovement::query()
        ->where('reference_type', Sale::class)
        ->where('reference_id', $sale->id)
        ->get();

    expect($movements)->toHaveCount(1);
});

it('throws exception when insufficient stock', function () use (&$paymentMethod): void {
    $batch = Batch::factory()->withQuantity(5)->create();
    $customer = Customer::factory()->create();

    $action = resolve(QuickSaleAction::class);

    $items = new DataCollection(SaleItemData::class, [
        new SaleItemData(
            product_id: $batch->product_id,
            batch_id: $batch->id,
            quantity: 10,
            unit_price: 500,
            unit_cost: 300,
        ),
    ]);

    $data = new QuickSaleData(
        customer_id: $customer->id,
        warehouse_id: $batch->warehouse_id,
        user_id: null,
        payment_method_id: $paymentMethod->id,
        sale_date: now(),
        paid_amount: 5000,
        note: null,
        items: $items,
    );

    $action->handle($data);
})->throws(RuntimeException::class, 'Insufficient stock');

it('handles no payment when paid_amount is zero', function () use (&$paymentMethod): void {
    $batch = Batch::factory()->withQuantity(100)->create();
    $customer = Customer::factory()->create();

    $action = resolve(QuickSaleAction::class);

    $items = new DataCollection(SaleItemData::class, [
        new SaleItemData(
            product_id: $batch->product_id,
            batch_id: $batch->id,
            quantity: 10,
            unit_price: 500,
            unit_cost: 300,
        ),
    ]);

    $data = new QuickSaleData(
        customer_id: $customer->id,
        warehouse_id: $batch->warehouse_id,
        user_id: null,
        payment_method_id: $paymentMethod->id,
        sale_date: now(),
        paid_amount: 0,
        note: null,
        items: $items,
    );

    $sale = $action->handle($data);

    expect($sale->payment_status)->toBe(PaymentStatusEnum::Unpaid)
        ->and($sale->paid_amount)->toBe(0);
});

it('throws exception when batch not found', function () use (&$paymentMethod): void {
    $batch = Batch::factory()->withQuantity(100)->create();
    $customer = Customer::factory()->create();

    $action = resolve(QuickSaleAction::class);

    $items = new DataCollection(SaleItemData::class, [
        new SaleItemData(
            product_id: $batch->product_id,
            batch_id: 99999,
            quantity: 10,
            unit_price: 500,
            unit_cost: 300,
        ),
    ]);

    $data = new QuickSaleData(
        customer_id: $customer->id,
        warehouse_id: $batch->warehouse_id,
        user_id: null,
        payment_method_id: $paymentMethod->id,
        sale_date: now(),
        paid_amount: 5000,
        note: null,
        items: $items,
    );

    $action->handle($data);
})->throws(RuntimeException::class, 'Batch not found');
