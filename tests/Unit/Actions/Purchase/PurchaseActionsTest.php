<?php

declare(strict_types=1);

use App\Actions\Purchase\CancelPurchase;
use App\Actions\Purchase\DeletePurchase;
use App\Actions\Purchase\OrderPurchase;
use App\Enums\PurchaseStatusEnum;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;

describe(DeletePurchase::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->warehouse = Warehouse::factory()->create();
        $this->supplier = Supplier::factory()->create();
    });

    it('may delete a pending purchase', function (): void {
        $purchase = Purchase::factory()->for($this->warehouse)->for($this->supplier)->pending()->create();

        $action = resolve(DeletePurchase::class);

        $result = $action->handle($purchase);

        expect($result)->toBeTrue()
            ->and(Purchase::query()->where('id', $purchase->id)->exists())->toBeFalse();
    });

    it('may delete a cancelled purchase', function (): void {
        $purchase = Purchase::factory()->for($this->warehouse)->for($this->supplier)->cancelled()->create();

        $action = resolve(DeletePurchase::class);

        $result = $action->handle($purchase);

        expect($result)->toBeTrue();
    });

    it('throws exception when deleting received purchase', function (): void {
        $purchase = Purchase::factory()->for($this->warehouse)->for($this->supplier)->received()->create();

        $action = resolve(DeletePurchase::class);

        expect(fn () => $action->handle($purchase))->toThrow(App\Exceptions\InvalidOperationException::class);
    });

    it('throws exception when deleting purchase with active payments', function (): void {
        $purchase = Purchase::factory()->for($this->warehouse)->for($this->supplier)->pending()->create();
        App\Models\Payment::factory()->forPurchase($purchase)->create(['status' => App\Enums\PaymentStateEnum::Active]);

        $action = resolve(DeletePurchase::class);

        expect(fn () => $action->handle($purchase))->toThrow(App\Exceptions\InvalidOperationException::class);
    });
});

describe(CancelPurchase::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->batch = Batch::factory()->for($this->product)->create(['quantity' => 100]);
        $this->warehouse = Warehouse::factory()->create();
        $this->supplier = Supplier::factory()->create();
    });

    it('may cancel a pending purchase', function (): void {
        $purchase = Purchase::factory()->for($this->warehouse)->for($this->supplier)->pending()->create();

        $action = resolve(CancelPurchase::class);

        $result = $action->handle($purchase);

        expect($result->status)->toBe(PurchaseStatusEnum::Cancelled);
    });

    it('may cancel an ordered purchase', function (): void {
        $purchase = Purchase::factory()->for($this->warehouse)->for($this->supplier)->ordered()->create();

        $action = resolve(CancelPurchase::class);

        $result = $action->handle($purchase);

        expect($result->status)->toBe(PurchaseStatusEnum::Cancelled);
    });

    it('throws exception when cancelling cancelled purchase', function (): void {
        $purchase = Purchase::factory()->for($this->warehouse)->for($this->supplier)->cancelled()->create();

        $action = resolve(CancelPurchase::class);

        expect(fn () => $action->handle($purchase))->toThrow(App\Exceptions\StateTransitionException::class);
    });

    it('throws exception when cancelling purchase with active payments', function (): void {
        $purchase = Purchase::factory()->for($this->warehouse)->for($this->supplier)->pending()->create();
        App\Models\Payment::factory()->forPurchase($purchase)->create(['status' => App\Enums\PaymentStateEnum::Active]);

        $action = resolve(CancelPurchase::class);

        expect(fn () => $action->handle($purchase))->toThrow(App\Exceptions\InvalidOperationException::class);
    });

    it('cancels ordered purchase with custom reason', function (): void {
        $purchase = Purchase::factory()->for($this->warehouse)->for($this->supplier)->ordered()->create();

        $action = resolve(CancelPurchase::class);

        $result = $action->handle($purchase, 'Supplier agreed to cancellation');

        expect($result->status)->toBe(PurchaseStatusEnum::Cancelled);
    });

    it('does not change stock when cancelling ordered purchase without receipt', function (): void {
        $purchase = Purchase::factory()->for($this->warehouse)->for($this->supplier)->ordered()->create();

        $action = resolve(CancelPurchase::class);

        $action->handle($purchase);

        expect($this->batch->fresh()->quantity)->toBe(100);
    });

    it('allows cancellation with only voided payments', function (): void {
        $purchase = Purchase::factory()->for($this->warehouse)->for($this->supplier)->pending()->create();
        App\Models\Payment::factory()->forPurchase($purchase)->create(['status' => App\Enums\PaymentStateEnum::Voided]);

        $action = resolve(CancelPurchase::class);

        $result = $action->handle($purchase);

        expect($result->status)->toBe(PurchaseStatusEnum::Cancelled);
    });
});

describe(OrderPurchase::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->warehouse = Warehouse::factory()->create();
        $this->supplier = Supplier::factory()->create();
    });

    it('may order a pending purchase', function (): void {
        $purchase = Purchase::factory()->for($this->warehouse)->for($this->supplier)->pending()->create();

        $action = resolve(OrderPurchase::class);

        $result = $action->handle($purchase);

        expect($result->status)->toBe(PurchaseStatusEnum::Ordered);
    });

    it('throws exception when ordering already ordered purchase', function (): void {
        $purchase = Purchase::factory()->for($this->warehouse)->for($this->supplier)->create(['status' => PurchaseStatusEnum::Ordered]);

        $action = resolve(OrderPurchase::class);

        expect(fn () => $action->handle($purchase))->toThrow(App\Exceptions\StateTransitionException::class);
    });

    it('throws exception when ordering cancelled purchase', function (): void {
        $purchase = Purchase::factory()->for($this->warehouse)->for($this->supplier)->cancelled()->create();

        $action = resolve(OrderPurchase::class);

        expect(fn () => $action->handle($purchase))->toThrow(App\Exceptions\StateTransitionException::class);
    });
});

use App\Actions\Purchase\UpdatePurchase;
use App\Data\Purchase\PurchaseData;
use App\Data\Purchase\PurchaseItemData;
use App\Enums\PaymentStatusEnum;
use Spatie\LaravelData\DataCollection;

describe(UpdatePurchase::class, function (): void {
    beforeEach(function (): void {
        $this->unit = Unit::factory()->create();
        $this->product = Product::factory()->for($this->unit)->create();
        $this->warehouse = Warehouse::factory()->create();
        $this->supplier = Supplier::factory()->create();
    });

    it('may update a pending purchase', function (): void {
        $purchase = Purchase::factory()->for($this->warehouse)->for($this->supplier)->pending()->create([
            'total_amount' => 10000,
        ]);

        $action = resolve(UpdatePurchase::class);

        $data = new PurchaseData(
            supplier_id: $this->supplier->id,
            warehouse_id: $this->warehouse->id,
            status: PurchaseStatusEnum::Pending,
            purchase_date: now(),
            total_amount: 15000,
            note: 'Updated note',
            items: new DataCollection(PurchaseItemData::class, []),
        );

        $result = $action->handle($purchase, $data);

        expect($result->total_amount)->toBe(15000);
    });

    it('throws exception when updating non-pending purchase', function (): void {
        $purchase = Purchase::factory()->for($this->warehouse)->for($this->supplier)->received()->create();

        $action = resolve(UpdatePurchase::class);

        $data = new PurchaseData(
            supplier_id: $this->supplier->id,
            warehouse_id: $this->warehouse->id,
            status: PurchaseStatusEnum::Pending,
            purchase_date: now(),
            total_amount: 10000,
            note: null,
            items: new DataCollection(PurchaseItemData::class, []),
        );

        expect(fn () => $action->handle($purchase, $data))
            ->toThrow(App\Exceptions\InvalidOperationException::class);
    });

    it('updates payment status when total amount changes', function (): void {
        $purchase = Purchase::factory()->for($this->warehouse)->for($this->supplier)->pending()->create([
            'total_amount' => 10000,
            'paid_amount' => 5000,
            'payment_status' => PaymentStatusEnum::Partial,
        ]);

        $action = resolve(UpdatePurchase::class);

        $data = new PurchaseData(
            supplier_id: $this->supplier->id,
            warehouse_id: $this->warehouse->id,
            status: PurchaseStatusEnum::Pending,
            purchase_date: now(),
            total_amount: 15000,
            note: null,
            items: new DataCollection(PurchaseItemData::class, []),
        );

        $result = $action->handle($purchase, $data);

        expect($result->total_amount)->toBe(15000);
        expect($result->payment_status)->toBe(PaymentStatusEnum::Partial);
    });

});
