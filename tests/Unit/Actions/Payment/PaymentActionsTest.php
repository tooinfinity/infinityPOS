<?php

declare(strict_types=1);

use App\Actions\Payment\RecordPayment;
use App\Actions\Payment\UpdatePaymentStatus;
use App\Actions\Payment\VoidPayment;
use App\Data\Payment\PaymentData;
use App\Enums\PaymentStateEnum;
use App\Enums\PaymentStatusEnum;
use App\Exceptions\InvalidPaymentMethodException;
use App\Exceptions\OverpaymentException;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;

describe(RecordPayment::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->warehouse = Warehouse::factory()->create();
        $this->customer = Customer::factory()->create();
        $this->supplier = Supplier::factory()->create();
        $this->paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);
    });

    it('may record a payment for a sale', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->create([
                'total_amount' => 10000,
                'paid_amount' => 0,
                'payment_status' => PaymentStatusEnum::Unpaid,
            ]);

        $data = new PaymentData(
            payment_method_id: $this->paymentMethod->id,
            amount: 5000,
            payment_date: now()->toDateString(),
            note: 'Test payment',
        );

        $action = resolve(RecordPayment::class);

        $payment = $action->handle($sale, $data);

        expect($payment)->toBeInstanceOf(Payment::class)
            ->and($payment->amount)->toBe(5000)
            ->and($payment->payment_method_id)->toBe($this->paymentMethod->id)
            ->and($payment->payable_id)->toBe($sale->id)
            ->and($payment->payable_type)->toBe($sale->getMorphClass())
            ->and($payment->status)->toBe(PaymentStateEnum::Active)
            ->and($payment->note)->toBe('Test payment');
    });

    it('updates sale payment status after recording payment', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->create([
                'total_amount' => 10000,
                'paid_amount' => 0,
                'payment_status' => PaymentStatusEnum::Unpaid,
            ]);

        $data = new PaymentData(
            payment_method_id: $this->paymentMethod->id,
            amount: 5000,
            payment_date: now()->toDateString(),
            note: 'Partial payment',
        );

        $action = resolve(RecordPayment::class);

        $action->handle($sale, $data);

        expect($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Partial)
            ->and($sale->fresh()->paid_amount)->toBe(5000);
    });

    it('marks sale as paid when full payment is made', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->create([
                'total_amount' => 10000,
                'paid_amount' => 0,
                'payment_status' => PaymentStatusEnum::Unpaid,
            ]);

        $data = new PaymentData(
            payment_method_id: $this->paymentMethod->id,
            amount: 10000,
            payment_date: now()->toDateString(),
            note: 'Full payment',
        );

        $action = resolve(RecordPayment::class);

        $action->handle($sale, $data);

        expect($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Paid)
            ->and($sale->fresh()->paid_amount)->toBe(10000);
    });

    it('records payment for a purchase', function (): void {
        $purchase = Purchase::factory()
            ->for($this->warehouse)
            ->for($this->supplier)
            ->create([
                'total_amount' => 15000,
                'paid_amount' => 0,
                'payment_status' => PaymentStatusEnum::Unpaid,
            ]);

        $data = new PaymentData(
            payment_method_id: $this->paymentMethod->id,
            amount: 7500,
            payment_date: now()->toDateString(),
            note: 'Purchase payment',
        );

        $action = resolve(RecordPayment::class);

        $payment = $action->handle($purchase, $data);

        expect($payment->payable_id)->toBe($purchase->id)
            ->and($payment->payable_type)->toBe($purchase->getMorphClass());
    });

    it('throws exception when payment method not found', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->create();

        $data = new PaymentData(
            payment_method_id: 99999,
            amount: 5000,
            payment_date: now()->toDateString(),
            note: 'Test',
        );

        $action = resolve(RecordPayment::class);

        expect(fn () => $action->handle($sale, $data))
            ->toThrow(InvalidPaymentMethodException::class);
    });

    it('throws exception when sale is already fully paid', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->create([
                'total_amount' => 10000,
                'paid_amount' => 10000,
                'payment_status' => PaymentStatusEnum::Paid,
            ]);

        $data = new PaymentData(
            payment_method_id: $this->paymentMethod->id,
            amount: 1000,
            payment_date: now()->toDateString(),
            note: 'Overpayment',
        );

        $action = resolve(RecordPayment::class);

        expect(fn () => $action->handle($sale, $data))
            ->toThrow(InvalidPaymentMethodException::class);
    });

    it('throws exception when overpayment occurs', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->create([
                'total_amount' => 10000,
                'paid_amount' => 0,
                'payment_status' => PaymentStatusEnum::Unpaid,
            ]);

        $data = new PaymentData(
            payment_method_id: $this->paymentMethod->id,
            amount: 15000,
            payment_date: now()->toDateString(),
            note: 'Overpayment',
        );

        $action = resolve(RecordPayment::class);

        expect(fn () => $action->handle($sale, $data))
            ->toThrow(OverpaymentException::class);
    });

    it('allows overpayment for walk-in sales without customer', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->create([
                'customer_id' => null,
                'total_amount' => 10000,
                'paid_amount' => 0,
                'payment_status' => PaymentStatusEnum::Unpaid,
            ]);

        $data = new PaymentData(
            payment_method_id: $this->paymentMethod->id,
            amount: 15000,
            payment_date: now()->toDateString(),
            note: 'Walk-in overpayment',
        );

        $action = resolve(RecordPayment::class);

        $payment = $action->handle($sale, $data);

        expect($payment->amount)->toBe(15000)
            ->and($sale->fresh()->change_amount)->toBe(5000)
            ->and($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Paid);
    });

    it('generates unique reference number for payment', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->create([
                'total_amount' => 50000,
                'paid_amount' => 0,
                'payment_status' => PaymentStatusEnum::Unpaid,
            ]);

        $data = new PaymentData(
            payment_method_id: $this->paymentMethod->id,
            amount: 5000,
            payment_date: now()->toDateString(),
            note: 'Test',
        );

        $action = resolve(RecordPayment::class);

        $payment1 = $action->handle($sale, $data);
        $payment2 = $action->handle($sale, $data);

        expect($payment1->reference_no)->not->toBe($payment2->reference_no);
    });

    it('loads payment method relationship', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->create([
                'total_amount' => 10000,
                'paid_amount' => 0,
                'payment_status' => PaymentStatusEnum::Unpaid,
            ]);

        $data = new PaymentData(
            payment_method_id: $this->paymentMethod->id,
            amount: 5000,
            payment_date: now()->toDateString(),
            note: 'Test',
        );

        $action = resolve(RecordPayment::class);

        $payment = $action->handle($sale, $data);

        expect($payment->relationLoaded('paymentMethod'))->toBeTrue()
            ->and($payment->paymentMethod)->toBeInstanceOf(PaymentMethod::class);
    });
});

describe(UpdatePaymentStatus::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->warehouse = Warehouse::factory()->create();
        $this->customer = Customer::factory()->create();
        $this->supplier = Supplier::factory()->create();
        $this->paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);
    });

    it('updates sale status to unpaid when no payments exist', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->create([
                'total_amount' => 10000,
                'paid_amount' => 0,
                'payment_status' => PaymentStatusEnum::Partial,
            ]);

        $action = resolve(UpdatePaymentStatus::class);

        $action->handle($sale);

        expect($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Unpaid)
            ->and($sale->fresh()->paid_amount)->toBe(0);
    });

    it('updates sale status to partial when partial payment exists', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->create([
                'total_amount' => 10000,
                'paid_amount' => 0,
                'payment_status' => PaymentStatusEnum::Unpaid,
            ]);

        Payment::factory()->forSale($sale)->create([
            'amount' => 5000,
            'status' => PaymentStateEnum::Active,
        ]);

        $action = resolve(UpdatePaymentStatus::class);

        $action->handle($sale);

        expect($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Partial)
            ->and($sale->fresh()->paid_amount)->toBe(5000);
    });

    it('updates sale status to paid when fully paid', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->create([
                'total_amount' => 10000,
                'paid_amount' => 0,
                'payment_status' => PaymentStatusEnum::Unpaid,
            ]);

        Payment::factory()->forSale($sale)->create([
            'amount' => 10000,
            'status' => PaymentStateEnum::Active,
        ]);

        $action = resolve(UpdatePaymentStatus::class);

        $action->handle($sale);

        expect($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Paid)
            ->and($sale->fresh()->paid_amount)->toBe(10000);
    });

    it('calculates change amount for walk-in sales', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->create([
                'customer_id' => null,
                'total_amount' => 10000,
                'paid_amount' => 0,
                'payment_status' => PaymentStatusEnum::Unpaid,
                'change_amount' => 0,
            ]);

        Payment::factory()->forSale($sale)->create([
            'amount' => 15000,
            'status' => PaymentStateEnum::Active,
        ]);

        $action = resolve(UpdatePaymentStatus::class);

        $action->handle($sale);

        expect($sale->fresh()->change_amount)->toBe(5000)
            ->and($sale->fresh()->paid_amount)->toBe(10000)
            ->and($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Paid);
    });

    it('updates purchase payment status', function (): void {
        $purchase = Purchase::factory()
            ->for($this->warehouse)
            ->for($this->supplier)
            ->create([
                'total_amount' => 15000,
                'paid_amount' => 0,
                'payment_status' => PaymentStatusEnum::Unpaid,
            ]);

        Payment::factory()->forPurchase($purchase)->create([
            'amount' => 7500,
            'status' => PaymentStateEnum::Active,
        ]);

        $action = resolve(UpdatePaymentStatus::class);

        $action->handle($purchase);

        expect($purchase->fresh()->payment_status)->toBe(PaymentStatusEnum::Partial)
            ->and($purchase->fresh()->paid_amount)->toBe(7500);
    });

    it('only counts active payments', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->create([
                'total_amount' => 10000,
                'paid_amount' => 0,
                'payment_status' => PaymentStatusEnum::Unpaid,
            ]);

        Payment::factory()->forSale($sale)->create([
            'amount' => 5000,
            'status' => PaymentStateEnum::Active,
        ]);

        Payment::factory()->forSale($sale)->create([
            'amount' => 5000,
            'status' => PaymentStateEnum::Voided,
        ]);

        $action = resolve(UpdatePaymentStatus::class);

        $action->handle($sale);

        expect($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Partial)
            ->and($sale->fresh()->paid_amount)->toBe(5000);
    });
});

describe(VoidPayment::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->warehouse = Warehouse::factory()->create();
        $this->customer = Customer::factory()->create();
        $this->paymentMethod = PaymentMethod::factory()->create(['is_active' => true]);
    });

    it('may void an active payment', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->create([
                'total_amount' => 10000,
                'paid_amount' => 10000,
                'payment_status' => PaymentStatusEnum::Paid,
            ]);

        $payment = Payment::factory()->forSale($sale)->create([
            'amount' => 10000,
            'status' => PaymentStateEnum::Active,
        ]);

        $action = resolve(VoidPayment::class);

        $result = $action->handle($payment, 'Customer requested refund');

        expect($result->status)->toBe(PaymentStateEnum::Voided)
            ->and($result->voided_by)->toBe(auth()->id())
            ->and($result->voided_at)->not->toBeNull()
            ->and($result->void_reason)->toBe('Customer requested refund');
    });

    it('updates payable payment status after voiding', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->create([
                'total_amount' => 10000,
                'paid_amount' => 10000,
                'payment_status' => PaymentStatusEnum::Paid,
            ]);

        $payment = Payment::factory()->forSale($sale)->create([
            'amount' => 10000,
            'status' => PaymentStateEnum::Active,
        ]);

        $action = resolve(VoidPayment::class);

        $action->handle($payment, 'Voided');

        expect($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Unpaid)
            ->and($sale->fresh()->paid_amount)->toBe(0);
    });

    it('partially updates payment status when multiple payments exist', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->create([
                'total_amount' => 10000,
                'paid_amount' => 10000,
                'payment_status' => PaymentStatusEnum::Paid,
            ]);

        $payment1 = Payment::factory()->forSale($sale)->create([
            'amount' => 5000,
            'status' => PaymentStateEnum::Active,
        ]);

        Payment::factory()->forSale($sale)->create([
            'amount' => 5000,
            'status' => PaymentStateEnum::Active,
        ]);

        $action = resolve(VoidPayment::class);

        $action->handle($payment1, 'Voided');

        expect($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Partial)
            ->and($sale->fresh()->paid_amount)->toBe(5000);
    });

    it('throws exception when voiding already voided payment', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->create();

        $payment = Payment::factory()->forSale($sale)->create([
            'amount' => 5000,
            'status' => PaymentStateEnum::Voided,
        ]);

        $action = resolve(VoidPayment::class);

        expect(fn () => $action->handle($payment, 'Double void'))
            ->toThrow(App\Exceptions\InvalidOperationException::class);
    });

    it('loads payment method and voided by relationships', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->create();

        $payment = Payment::factory()->forSale($sale)->create([
            'amount' => 5000,
            'status' => PaymentStateEnum::Active,
        ]);

        $action = resolve(VoidPayment::class);

        $result = $action->handle($payment, 'Test void');

        expect($result->relationLoaded('paymentMethod'))->toBeTrue()
            ->and($result->relationLoaded('voidedBy'))->toBeTrue();
    });

    it('works within a transaction', function (): void {
        $sale = Sale::factory()
            ->for($this->warehouse)
            ->for($this->customer)
            ->create([
                'total_amount' => 10000,
                'paid_amount' => 10000,
                'payment_status' => PaymentStatusEnum::Paid,
            ]);

        $payment = Payment::factory()->forSale($sale)->create([
            'amount' => 10000,
            'status' => PaymentStateEnum::Active,
        ]);

        $action = resolve(VoidPayment::class);

        Illuminate\Support\Facades\DB::beginTransaction();

        try {
            $action->handle($payment, 'Test');
            throw new Exception('Force rollback');
        } catch (Exception) {
            Illuminate\Support\Facades\DB::rollBack();
        }

        expect($payment->fresh()->status)->toBe(PaymentStateEnum::Active)
            ->and($sale->fresh()->payment_status)->toBe(PaymentStatusEnum::Paid);
    });
});
