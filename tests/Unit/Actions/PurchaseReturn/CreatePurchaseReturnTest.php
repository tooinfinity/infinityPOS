<?php

declare(strict_types=1);

use App\Actions\PurchaseReturn\CreatePurchaseReturn;
use App\Data\PurchaseReturn\CreatePurchaseReturnData;
use App\Data\PurchaseReturn\PurchaseReturnItemData;
use App\Enums\PaymentStatusEnum;
use App\Enums\PurchaseStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Exceptions\InvalidOperationException;
use App\Models\Batch;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Warehouse;
use Spatie\LaravelData\DataCollection;

it('creates a pending purchase return with items', function (): void {
    $purchase = Purchase::factory()->received()->create();
    $warehouse = Warehouse::factory()->create();
    $batch = Batch::factory()->withQuantity(100)->create();

    $action = resolve(CreatePurchaseReturn::class);

    $items = new DataCollection(PurchaseReturnItemData::class, [
        new PurchaseReturnItemData(
            product_id: $batch->product_id,
            batch_id: $batch->id,
            quantity: 10,
            unit_cost: 500,
        ),
    ]);

    $data = new CreatePurchaseReturnData(
        purchase_id: $purchase->id,
        warehouse_id: $warehouse->id,
        user_id: null,
        return_date: now(),
        note: 'Test return',
        items: $items,
    );

    $purchaseReturn = $action->handle($data);

    expect($purchaseReturn)
        ->toBeInstanceOf(PurchaseReturn::class)
        ->and($purchaseReturn->purchase_id)->toBe($purchase->id)
        ->and($purchaseReturn->warehouse_id)->toBe($warehouse->id)
        ->and($purchaseReturn->reference_no)->toStartWith('PUR-RETURN-')
        ->and($purchaseReturn->status)->toBe(ReturnStatusEnum::Pending)
        ->and($purchaseReturn->payment_status)->toBe(PaymentStatusEnum::Unpaid)
        ->and($purchaseReturn->total_amount)->toBe(5000)
        ->and($purchaseReturn->paid_amount)->toBe(0);
});

it('auto-generates unique reference number', function (): void {
    $purchase = Purchase::factory()->received()->create();
    $warehouse = Warehouse::factory()->create();
    $batch = Batch::factory()->withQuantity(100)->create();

    $action = resolve(CreatePurchaseReturn::class);

    $items = new DataCollection(PurchaseReturnItemData::class, [
        new PurchaseReturnItemData(
            product_id: $batch->product_id,
            batch_id: $batch->id,
            quantity: 5,
            unit_cost: 100,
        ),
    ]);

    $data = new CreatePurchaseReturnData(
        purchase_id: $purchase->id,
        warehouse_id: $warehouse->id,
        user_id: null,
        return_date: now(),
        note: null,
        items: $items,
    );

    $purchaseReturn = $action->handle($data);

    expect($purchaseReturn->reference_no)
        ->toStartWith('PUR-RETURN-')
        ->and(mb_strlen($purchaseReturn->reference_no))->toBeGreaterThan(10);
});

it('creates purchase return with multiple items', function (): void {
    $purchase = Purchase::factory()->received()->create();
    $warehouse = Warehouse::factory()->create();
    $batch1 = Batch::factory()->withQuantity(100)->create();
    $batch2 = Batch::factory()->withQuantity(100)->create();

    $action = resolve(CreatePurchaseReturn::class);

    $items = new DataCollection(PurchaseReturnItemData::class, [
        new PurchaseReturnItemData(
            product_id: $batch1->product_id,
            batch_id: $batch1->id,
            quantity: 10,
            unit_cost: 100,
        ),
        new PurchaseReturnItemData(
            product_id: $batch2->product_id,
            batch_id: $batch2->id,
            quantity: 5,
            unit_cost: 200,
        ),
    ]);

    $data = new CreatePurchaseReturnData(
        purchase_id: $purchase->id,
        warehouse_id: $warehouse->id,
        user_id: null,
        return_date: now(),
        note: null,
        items: $items,
    );

    $purchaseReturn = $action->handle($data);

    expect(PurchaseReturnItem::query()->where('purchase_return_id', $purchaseReturn->id)->count())->toBe(2)
        ->and($purchaseReturn->total_amount)->toBe(2000);
});

it('throws exception when creating return for a non-received purchase', function (PurchaseStatusEnum $status): void {
    $purchase = Purchase::factory()->create(['status' => $status]);
    $warehouse = Warehouse::factory()->create();

    $data = new CreatePurchaseReturnData(
        purchase_id: $purchase->id,
        warehouse_id: $warehouse->id,
        user_id: null,
        return_date: now(),
        note: null,
        items: new DataCollection(PurchaseReturnItemData::class, []),
    );

    $action = resolve(CreatePurchaseReturn::class);

    expect(fn () => $action->handle($data))
        ->toThrow(InvalidOperationException::class);
})->with([
    [PurchaseStatusEnum::Pending],
    [PurchaseStatusEnum::Ordered],
    [PurchaseStatusEnum::Cancelled],
]);
