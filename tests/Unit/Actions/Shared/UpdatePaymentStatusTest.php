<?php

declare(strict_types=1);

use App\Actions\Shared\UpdatePaymentStatus;
use App\Enums\PaymentStatusEnum;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Warehouse;

it('updates payment status to paid when fully paid', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
        'payment_status' => PaymentStatusEnum::Unpaid,
    ]);

    Payment::factory()->forSale($sale)->create(['amount' => 1000]);

    $action = resolve(UpdatePaymentStatus::class);
    $action->handle($sale);

    $sale = $sale->fresh();
    expect($sale->paid_amount)->toBe(1000)
        ->and($sale->payment_status)->toBe(PaymentStatusEnum::Paid)
        ->and($sale->change_amount)->toBe(0);
});

it('updates payment status to partial when partially paid', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
        'payment_status' => PaymentStatusEnum::Unpaid,
    ]);

    Payment::factory()->forSale($sale)->create(['amount' => 500]);

    $action = resolve(UpdatePaymentStatus::class);
    $action->handle($sale);

    $sale = $sale->fresh();
    expect($sale->paid_amount)->toBe(500)
        ->and($sale->payment_status)->toBe(PaymentStatusEnum::Partial);
});

it('updates payment status to unpaid when no payments', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create([
        'total_amount' => 1000,
        'paid_amount' => 500,
        'payment_status' => PaymentStatusEnum::Paid,
    ]);

    $action = resolve(UpdatePaymentStatus::class);
    $action->handle($sale);

    $sale = $sale->fresh();
    expect($sale->paid_amount)->toBe(0)
        ->and($sale->payment_status)->toBe(PaymentStatusEnum::Unpaid);
});

it('calculates change amount for overpayment on sale', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
        'payment_status' => PaymentStatusEnum::Unpaid,
    ]);

    Payment::factory()->forSale($sale)->create(['amount' => 1500]);

    $action = resolve(UpdatePaymentStatus::class);
    $action->handle($sale);

    $sale = $sale->fresh();
    expect($sale->paid_amount)->toBe(1000)
        ->and($sale->change_amount)->toBe(500)
        ->and($sale->payment_status)->toBe(PaymentStatusEnum::Paid);
});

it('caps paid amount at total amount', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
        'payment_status' => PaymentStatusEnum::Unpaid,
    ]);

    Payment::factory()->forSale($sale)->create(['amount' => 500]);
    Payment::factory()->forSale($sale)->create(['amount' => 1000]);

    $action = resolve(UpdatePaymentStatus::class);
    $action->handle($sale);

    $sale = $sale->fresh();
    expect($sale->paid_amount)->toBe(1000)
        ->and($sale->payment_status)->toBe(PaymentStatusEnum::Paid);
});

it('ignores voided payments', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
        'payment_status' => PaymentStatusEnum::Unpaid,
    ]);

    Payment::factory()->forSale($sale)->voided()->create(['amount' => 1000]);

    $action = resolve(UpdatePaymentStatus::class);
    $action->handle($sale);

    $sale = $sale->fresh();
    expect($sale->paid_amount)->toBe(0)
        ->and($sale->payment_status)->toBe(PaymentStatusEnum::Unpaid);
});

it('works with purchase return', function (): void {
    $warehouse = Warehouse::factory()->create();
    $purchase = Purchase::factory()->forWarehouse($warehouse)->create();
    $purchaseReturn = PurchaseReturn::factory()->forPurchase($purchase)->pending()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
        'payment_status' => PaymentStatusEnum::Unpaid,
    ]);

    Payment::factory()->forPurchaseReturn($purchaseReturn)->create(['amount' => 1000]);

    $action = resolve(UpdatePaymentStatus::class);
    $action->handle($purchaseReturn);

    $purchaseReturn = $purchaseReturn->fresh();
    expect($purchaseReturn->paid_amount)->toBe(1000)
        ->and($purchaseReturn->payment_status)->toBe(PaymentStatusEnum::Paid);
});

it('does not set change amount for non-sale', function (): void {
    $warehouse = Warehouse::factory()->create();
    $purchase = Purchase::factory()->forWarehouse($warehouse)->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
        'payment_status' => PaymentStatusEnum::Unpaid,
    ]);

    Payment::factory()->forPurchase($purchase)->create(['amount' => 1500]);

    $action = resolve(UpdatePaymentStatus::class);
    $action->handle($purchase);

    $purchase = $purchase->fresh();
    expect($purchase->paid_amount)->toBe(1000)
        ->and($purchase->payment_status)->toBe(PaymentStatusEnum::Paid);
});

it('handles sale return', function (): void {
    $warehouse = Warehouse::factory()->create();
    $sale = Sale::factory()->forWarehouse($warehouse)->create();
    $saleReturn = SaleReturn::factory()->forSale($sale)->pending()->create([
        'total_amount' => 1000,
        'paid_amount' => 0,
        'payment_status' => PaymentStatusEnum::Unpaid,
    ]);

    Payment::factory()->forSaleReturn($saleReturn)->create(['amount' => 500]);

    $action = resolve(UpdatePaymentStatus::class);
    $action->handle($saleReturn);

    $saleReturn = $saleReturn->fresh();
    expect($saleReturn->paid_amount)->toBe(500)
        ->and($saleReturn->payment_status)->toBe(PaymentStatusEnum::Partial);
});
